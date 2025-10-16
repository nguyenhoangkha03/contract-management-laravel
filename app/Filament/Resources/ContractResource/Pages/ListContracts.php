<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractParticipant;
use App\Models\ContractProduct;
use App\Models\ContractTerm;
use App\Models\ContractType;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;

class ListContracts extends ListRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importContract')
                ->label('Import hợp đồng')
                ->form([
                    FileUpload::make('contract_file')
                        ->label('Tệp hợp đồng')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->required()
                        ->disk('public')
                        ->directory('temp-uploads'),
                ])
                ->action(function (array $data): void {
                    $this->processContractImport($data['contract_file']);
                }),
            Actions\CreateAction::make()->label('Lập hợp đồng'),
        ];
    }

    protected function tryParseDate(string $date): ?Carbon
    {
        $date = trim(preg_replace('/\s+/', ' ', $date));

        if (empty($date)) {
            return null;
        }

        $formats = [
            'd tháng m năm Y',
            'd tháng n năm Y',
            'd/m/Y',
            'd-m-Y',
            'd m Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date);
            } catch (\Exception $e) {
                continue;
            }
        }

        if (preg_match('/(?:ngày)?\s*(\d{1,2})\s*(?:tháng)\s*(\d{1,2})\s*(?:năm)\s*(\d{4})/i', $date, $matches)) {
            try {
                return Carbon::createFromDate($matches[3], $matches[2], $matches[1]);
            } catch (\Exception $e) {
            }
        }

        if (preg_match('/(\d{1,2})[\/\-\s]+(\d{1,2})[\/\-\s]+(\d{4})/', $date, $matches)) {
            try {
                return Carbon::createFromDate($matches[3], $matches[2], $matches[1]);
            } catch (\Exception $e) {
            }
        }

        return null;
    }


    protected function processContractImport(string $filePath): void
    {
        try {
            DB::beginTransaction();

            $fullPath = Storage::disk('public')->path($filePath);
            $contractData = $this->extractDataFromWordFile($fullPath);

            if (empty($contractData['start_date_full'])) {
                throw new \Exception('Không thể trích xuất ngày ký từ hợp đồng');
            }

            $contractType = ContractType::firstOrCreate(
                ['name' => mb_strtolower($contractData['contract_type'])],
                ['name' => ucwords($contractData['contract_type'])]
            );

            // dd($contractData);

            $client = Client::firstOrCreate(
                ['name' => $contractData['b_name']],
                [
                    'name' => $contractData['b_name'],
                    'address' => $contractData['b_address'],
                    'phone' => $contractData['b_phone'],
                ]
            );

            $signDate = $this->tryParseDate($contractData['start_date_full']);
            if (!$signDate) {
                throw new \Exception('Không thể phân tích ngày ký: "' . $contractData['start_date_full'] . '"');
            }

            $startDate = null;
            $endDate = null;

            try {
                $startDate = Carbon::createFromFormat('d/m/Y', $contractData['start_date']);
            } catch (\Exception $e) {
                throw new \Exception('Không thể phân tích ngày bắt đầu: "' . $contractData['start_date'] . '"');
            }

            try {
                $endDate = Carbon::createFromFormat('d/m/Y', $contractData['end_date']);
            } catch (\Exception $e) {
                throw new \Exception('Không thể phân tích ngày kết thúc: "' . $contractData['end_date'] . '"');
            }

            // dd($contractData);

            $contract = Contract::create([
                'contract_number' => $contractData['contract_number'],
                'contract_type_id' => $contractType->id,
                'client_id' => $client->id,
                'total_value' => $this->parseNumberFormat($contractData['grand_total']),
                'contract_purpose' => $contractData['contract_purpose'],
                'sign_date' => $signDate->startOfDay(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'legal_basis' => implode('; ', $contractData['legal_basis']),
                'contract_form' => $contractData['contract_form'],
                'payment_terms' => $contractData['payment_terms'],
                'payment_requirements' => implode('; ', $contractData['payment_requirements']),
                'pay_method' => $contractData['pay_method'] === 'Chuyển khoản' ? 'transfer' : 'cash',
            ]);

            $participantB = ContractParticipant::create([
                'contract_id' => $contract->id,
                'party_type' => 'B',
                'address' => $contractData['b_address'],
                'phone' => $contractData['b_phone'],
                'tax_code' => $contractData['b_tax_code'],
                'full_name' => $contractData['b_representative'],
                'representative_position' => $contractData['b_representative_pos'],
                'bank_name' => $contractData['b_bank_name'],
                'bank_account' => $contractData['b_bank_account'],
            ]);

            foreach ($contractData['products'] as $index => $productData) {
                $product = Product::firstOrCreate(
                    ['name' => $productData['product_name']],
                    [
                        'name' => $productData['product_name'],
                        'unit' => $productData['unit'],
                        'price' => $this->parseNumberFormat($productData['unit_price']),
                    ]
                );

                ContractProduct::create([
                    'contract_id' => $contract->id,
                    'product_id' => $product->id,
                    'number' => $productData['qty'],
                    'total' => $this->parseNumberFormat($productData['total']),
                ]);
            }

            // if (isset($contractData['contract_terms'])) {
            //     foreach ($contractData['contract_terms'] as $index => $term) {
            //         ContractTerm::create([
            //             'contract_id' => $contract->id,
            //             'term_type' => $term['term_type'],
            //             'conent' => implode('; ', $term['content_items']),
            //             'order' => $index + 1,
            //         ]);
            //     }
            // }

            DB::commit();

            Storage::disk('public')->delete($filePath);

            Notification::make()
                ->title('Nhập hợp đồng thành công')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Lỗi nhập hợp đồng')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt($e->getMessage());
        }
    }

    protected function extractDataFromWordFile(string $filePath): array
    {
        $phpWord = IOFactory::load($filePath);
        $sections = $phpWord->getSections();
        $documentText = $this->extractTextFromSections($sections);

        $data = [];

        $documentText = $this->normalizeText($documentText);

        preg_match('/Số:\s*(\d{6,})/iu', $documentText, $contractNumberMatches);
        $data['contract_number'] = $contractNumberMatches[1] ?? '';

        preg_match('/V\/v:\s*CUNG CẤP\s+(.*?)(?=\r\n|\n|\r)/i', $documentText, $purposeMatches);
        $data['contract_purpose'] = trim($purposeMatches[1] ?? '');
        $data['contract_type'] = 'HỢP ĐỒNG KINH TẾ';

        $beforeToday = $this->extractBetween($documentText, '', 'Hôm nay');

        $legalBasis = [];
        if (!empty($beforeToday)) {
            $lines = explode("\n", $beforeToday);

            foreach ($lines as $line) {
                $line = trim($line);
                if (mb_stripos($line, 'Căn cứ') === 0) {
                    $legalBasis[] = $line;
                }
            }
        }

        $data['legal_basis'] = $legalBasis;

        preg_match('/Hôm nay,\s*ngày\s+(\d{1,2})\s+tháng\s+(\d{1,2})\s+năm\s+(\d{4})/iu', $documentText, $startDateFullMatches);
        if (!empty($startDateFullMatches)) {
            $day = $startDateFullMatches[1] ?? '';
            $month = $startDateFullMatches[2] ?? '';
            $year = $startDateFullMatches[3] ?? '';
            if ($day && $month && $year) {
                $data['start_date_full'] = "$day tháng $month năm $year";
            } else {
                preg_match('/Hôm nay,\s*ngày\s+(.*?)(?=,|chúng tôi)/iu', $documentText, $altDateMatches);
                $data['start_date_full'] = trim($altDateMatches[1] ?? '');
            }
        } else {
            preg_match('/Hôm nay,\s*ngày\s+(.*?)(?=,|chúng tôi)/iu', $documentText, $altDateMatches);
            $data['start_date_full'] = trim($altDateMatches[1] ?? '');
        }

        if (empty($data['start_date_full'])) {
            $today = date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
            $data['start_date_full'] = $today;
        }

        preg_match('/Thời gian thực hiện hợp đồng từ ngày\s*(\d{1,2}\/\d{1,2}\/\d{4})\s*đến\s*(\d{1,2}\/\d{1,2}\/\d{4})/i', $documentText, $dateRangeMatches);
        $data['start_date'] = trim($dateRangeMatches[1] ?? '');
        $data['end_date'] = trim($dateRangeMatches[2] ?? '');

        $data['a_address'] = $this->extractValueAfter($documentText, 'BÊN A', 'Địa chỉ', 'Điện thoại');
        // $data['a_phone'] = $this->extractValueAfter($documentText, 'Điện thoại', 'Mã số thuế');

        $aSection = $this->extractBetween($documentText, 'BÊN A', 'BÊN B');
        $data['a_phone'] = $this->extractBetween($aSection, 'Điện thoại', 'Mã số thuế');

        $data['a_tax_code'] = $this->extractBetween($aSection, 'Mã số thuế', 'Đại diện');
        $data['a_representative'] = $this->extractBetween($documentText, 'Đại diện', 'Chức vụ');
        $data['a_representative_pos'] = $this->extractAfter($documentText, 'Chức vụ', 'Tên tài khoản');
        // $data['a_bank_name'] = $this->extractValueAfter($documentText, 'Tên tài khoản', 'Số tài khoản');
        // $data['a_bank_account'] = $this->extractValueAfter($documentText, 'Số tài khoản', 'BÊN B');
        $data['a_bank_name'] = $this->extractBetween($aSection, 'Tên tài khoản', 'Số tài khoản');
        $data['a_bank_account'] = $this->extractAfter($aSection, 'Số tài khoản');

        // dd($aSection);

        $data['b_name'] = $this->extractAfter($documentText, 'BÊN B', 'Địa chỉ');
        $data['b_address'] = $this->extractValueAfter($documentText, 'BÊN B', 'Địa chỉ', 'Điện thoại');
        $data['b_phone'] = $this->extractValueAfter($documentText, 'BÊN B', 'Điện thoại', 'Mã số thuế');
        $data['b_tax_code'] = $this->extractValueAfter($documentText, 'BÊN B', 'Mã số thuế', 'Đại diện');

        $bSection = $this->extractBetween($documentText, 'BÊN B', 'Qua trao đổi');
        $data['b_representative'] = $this->extractBetween($bSection, 'Đại diện', 'Chức vụ');
        $data['b_representative_pos'] = $this->extractAfter($bSection, 'Chức vụ', 'Tên tài khoản');
        $data['b_bank_name'] = $this->extractValueAfter($documentText, 'BÊN B', 'Tên tài khoản', 'Số tài khoản');
        $data['b_bank_account'] = $this->extractValueAfter($documentText, 'BÊN B', 'Số tài khoản', 'Qua trao đổi');

        // $productsText = $this->extractBetween($documentText, 'GIÁ TRỊ HỢP ĐỒNG', 'TỔNG CỘNG');
        // $products = [];

        // if (!empty($productsText)) {
        //     preg_match_all('/(\d+)\s+\*\*(.*?)\*\*\s+(.*?)\s+(\d{1,3}(?:,\d{3})*)\s+(\d+)\s+(\d{1,3}(?:,\d{3})*)/i', $productsText, $productMatches, PREG_SET_ORDER);
        //     foreach ($productMatches as $match) {
        //         $products[] = [
        //             'no' => $match[1],
        //             'product_name' => trim($match[2]),
        //             'unit' => trim($match[3]),
        //             'unit_price' => trim($match[4]),
        //             'qty' => trim($match[5]),
        //             'total' => trim($match[6]),
        //         ];
        //     }
        // }

        // $data['products'] = $products;

        $lines = explode("\n", $documentText);
        $products = [];
        $startCollect = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'TT')) {
                $startCollect = true;
                continue;
            }

            if ($startCollect) {
                if (stripos($line, 'TỔNG CỘNG') !== false) {
                    break;
                }

                $columns = preg_split('/\t+|\s{2,}/', $line);

                if (count($columns) >= 6) {
                    $products[] = [
                        'no' => trim($columns[0]),
                        'product_name' => trim($columns[1]),
                        'unit' => trim($columns[2]),
                        'unit_price' => trim($columns[3]),
                        'qty' => trim($columns[4]),
                        'total' => trim($columns[5]),
                    ];
                }
            }
        }
        $data['products'] = $products;

        $data['grand_total'] = '';
        foreach (explode("\n", $documentText) as $line) {
            if (preg_match('/TỔNG CỘNG\s+([0-9.,]+)/iu', $line, $match)) {
                $data['grand_total'] = trim($match[1]);
                break;
            }
        }

        preg_match('/Hình thức hợp đồng:\s*(.+)/i', $documentText, $match);
        $data['contract_form'] = trim($match[1] ?? '');

        preg_match('/Thanh toán:\s*(.+)/i', $documentText, $match);
        $data['payment_terms'] = trim($match[1] ?? '');

        $paymentReqText = $this->extractBetween($documentText, 'Hồ sơ thanh toán gồm:', 'Hình thức thanh toán');
        $paymentRequirements = [];

        if (!empty($paymentReqText)) {
            $paymentReqLines = explode("\n", $paymentReqText);
            foreach ($paymentReqLines as $line) {
                $line = trim($line);
                if (!empty($line) && strpos($line, '+') === 0) {
                    $paymentRequirements[] = trim(substr($line, 1));
                }
            }
        }

        $data['payment_requirements'] = $paymentRequirements;

        $data['payment_terms'] = $this->extractBetween($documentText, 'Thanh toán:', '- Hồ sơ thanh toán');

        preg_match('/3\.3\.\s*Hình thức thanh toán:.*hình thức\s+([a-zA-ZÀ-ỹ\s]+)/iu', $documentText, $match);
        $data['pay_method'] = trim(mb_strtolower($match[1] ?? ''));


        // $termSection = $this->extractBetween($documentText, 'QUYỀN VÀ TRÁCH NHIỆM CỦA BÊN A', 'ĐẠI DIỆN BÊN B');
        // $termSection = "QUYỀN VÀ TRÁCH NHIỆM CỦA BÊN A\n" . $termSection;

        // $lines = explode("\n", $termSection);

        // function isTermHeader(string $line): ?string
        // {
        //     $line = trim($line);

        //     if (preg_match('/^ĐIỀU\s+\d+[:：\-]?\s*.+$/iu', $line)) {
        //         return $line;
        //     }

        //     $keywords = [
        //         'QUYỀN VÀ TRÁCH NHIỆM CỦA BÊN A',
        //         'QUYỀN VÀ TRÁCH NHIỆM CỦA BÊN B',
        //         'ĐẢM BẢO TƯ CÁCH PHÁP NHÂN',
        //         'HIỆU LỰC CỦA CÁC ĐIỀU KHOẢN',
        //         'TRƯỜNG HỢP BẤT KHẢ KHÁNG',
        //         'THỦ TỤC GIẢI QUYẾT TRANH CHẤP HỢP ĐỒNG',
        //         'TỔNG THỂ',
        //     ];

        //     foreach ($keywords as $keyword) {
        //         if (mb_stripos($line, $keyword) === 0) {
        //             return $line;
        //         }
        //     }

        //     return null;
        // }

        // $termsData = [];
        // $currentTerm = null;
        // $currentContents = [];
        // $order = 1;

        // foreach ($lines as $line) {
        //     $line = trim($line);
        //     if ($line === '') continue;

        //     $newTerm = isTermHeader($line);

        //     if ($newTerm) {
        //         if ($currentTerm && !empty($currentContents)) {
        //             $termsData[] = [
        //                 'term_type' => $currentTerm,
        //                 'content_items' => array_map(fn($item) => rtrim($item, ';') . ';', $currentContents),
        //                 'order' => $order++,
        //             ];
        //         }

        //         $currentTerm = $newTerm;
        //         $currentContents = [];
        //         continue;
        //     }

        //     if ($currentTerm !== null) {
        //         if (str_starts_with($line, '-') || str_starts_with($line, '+') || str_starts_with($line, '•')) {
        //             $currentContents[] = ltrim($line, '-+• ');
        //         } elseif (!empty($currentContents)) {
        //             $currentContents[count($currentContents) - 1] .= ' ' . $line;
        //         } else {
        //             $currentContents[] = $line;
        //         }
        //     }
        // }

        // if ($currentTerm && !empty($currentContents)) {
        //     $termsData[] = [
        //         'term_type' => $currentTerm,
        //         'content_items' => array_map(fn($item) => rtrim($item, ';') . ';', $currentContents),
        //         'order' => $order++,
        //     ];
        // }

        // $data['contract_terms'] = $termsData;













        return $data;
    }

    protected function extractTextFromSections(array $sections): string
    {
        $text = '';

        foreach ($sections as $section) {
            $elements = $section->getElements();

            foreach ($elements as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    $text .= $this->getTextRunContent($element) . "\n";
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $text .= $element->getText() . "\n";
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                    $rows = $element->getRows();
                    foreach ($rows as $row) {
                        $cells = $row->getCells();
                        $rowText = '';
                        foreach ($cells as $cell) {
                            $rowText .= $this->getTextFromCell($cell) . "\t";
                        }
                        $text .= trim($rowText) . "\n";
                    }
                }
            }
        }

        return $text;
    }

    protected function getTextRunContent(\PhpOffice\PhpWord\Element\TextRun $textRun): string
    {
        $text = '';
        foreach ($textRun->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                $text .= $element->getText();
            }
        }
        return $text;
    }

    protected function getTextFromCell(\PhpOffice\PhpWord\Element\Cell $cell): string
    {
        $text = '';
        foreach ($cell->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                $text .= $this->getTextRunContent($element);
            } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                $text .= $element->getText();
            } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {

                $rows = $element->getRows();
                foreach ($rows as $row) {
                    $cells = $row->getCells();
                    foreach ($cells as $nestedCell) {
                        $text .= $this->getTextFromCell($nestedCell) . " ";
                    }
                }
            }
        }
        return $text;
    }

    protected function extractBetween(string $text, string $start, string $end): string
    {
        $pattern = '/' . preg_quote($start, '/') . '(.*?)' . preg_quote($end, '/') . '/is';
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    protected function extractAfter(string $text, string $marker, string $endMarker = null): string
    {
        $pattern = '/' . preg_quote($marker, '/') . '\s*(.*?)' . ($endMarker ? '(?=' . preg_quote($endMarker, '/') . ')' : '$') . '/is';
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    protected function extractValueAfter(string $text, string $section, string $label, string $nextLabel = null): string
    {
        $sectionPattern = '/' . preg_quote($section, '/') . '.*?' . preg_quote($label, '/') . '(.*?)' .
            ($nextLabel ? '(?=' . preg_quote($nextLabel, '/') . ')' : '$') . '/is';

        if (preg_match($sectionPattern, $text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    protected function parseNumberFormat(string $number): float
    {
        $number = preg_replace('/[^0-9,.]/', '', $number);

        $number = str_replace('.', '', $number);

        return (float) $number;
    }

    protected function normalizeText(string $text): string
    {
        // Nếu bạn dùng PHP >=8.2 có thể dùng normalizer
        if (class_exists('Normalizer')) {
            return \Normalizer::normalize($text, \Normalizer::FORM_C);
        }

        // Nếu không hỗ trợ, fallback về cách thủ công (loại bỏ ký tự tổ hợp thường gặp)
        $text = str_replace(["\u{0301}", "\u{0300}", "\u{0323}"], '', $text); // sắc, huyền, nặng
        return $text;
    }


    public function getTitle(): string
    {
        return 'Danh Sách Hợp Đồng';
    }
}
