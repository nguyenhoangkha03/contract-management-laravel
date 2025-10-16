<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractParticipant;
use App\Models\ContractProduct;
use App\Models\ContractStatus;
use App\Models\ContractTerm;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\ExportTemplate;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Tabs as InfolistTabs;
use Filament\Infolists\Components\Tabs\Tab as InfolistTab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class ContractDetail extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ContractResource::class;

    protected static string $view = 'filament.resources.contract-resource.pages.contract-detail';

    protected static ?string $title = 'Chi Tiết Hợp Đồng';

    public Contract $record;

    public $data = [];
    public $templates;

    public function mount($record): void
    {
        $this->record = $record;

        $formData = $this->loadForm();

        $this->form->fill($formData);

        // $this->fillForm($formData);

        $this->templates = ExportTemplate::query()
            ->where('contract_type_id', $record->contract_type_id)
            ->get();
    }

    public function getContractProductCountProperty(): int
    {
        return ContractProduct::where('contract_id', $this->record->id)->count();
    }

    public function ContractTypeName(): string
    {
        return $this->record->contractType->name;
    }

    public function ClientName(): string
    {
        return $this->record->client->name;
    }

    protected function loadForm(): array
    {
        $contractParticipantA = ContractParticipant::where('party_type', 'A')->get()->first();
        $contractParticipantB = ContractParticipant::where('contract_id', $this->record->id)->get()->first();

        $contractProducts = $this->record->contractProducts->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->number,
                'unit' => $item->product->unit,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'description' => $item->description,
            ];
        })->toArray();

        return [
            'contract_number' => $this->record->contract_number,
            'contract_type_id' => $this->record->contract_type_id,
            'contract_client_id' => $this->record->client->id,
            'contract_purpose' => $this->record->contract_purpose,
            'contract_form' => $this->record->contract_form,
            'contract_client_address' => $this->record->client->address,
            'contract_start_date' => $this->record->start_date,
            'contract_sign_date' => $this->record->sign_date,
            'contract_end_date' => $this->record->end_date,
            'contract_day_number' => Carbon::parse($this->record->start_date)->diffInDays(Carbon::parse($this->record->end_date)),
            'contract_payment_method' => $this->record->pay_method ?? '',
            'contract_total_value' => (int) $this->record->total_value,
            'contract_payment_terms' => $this->record->payment_terms ?? '',
            'contract_payment_requirements' => $this->record->payment_requirements ?? '',
            'contract_legal_basis' => $this->record->legal_basis ?? '',
            'contract_payment_status' => $this->record->pay ?? '',
            'contract_liquidation_status' => $this->record->liquidation ?? '',

            // Ben A
            'contract_participant_A' => $contractParticipantA->full_name ?? '',
            'contract_participant_pos_A' => $contractParticipantA->representative_position ?? '',
            'contract_participant_tax_code_A' => $contractParticipantA->tax_code ?? '',
            'contract_participant_address_A' => $contractParticipantA->address ?? '',
            'contract_participant_bank_account_A' => $contractParticipantA->bank_account ?? '',
            'contract_participant_bank_name_A' => $contractParticipantA->bank_name ?? '',
            'contract_participant_phone_A' => $contractParticipantA->phone ?? '',
            'contract_participant_email_A' => $contractParticipantA->email ?? '',

            // Ben B
            'contract_participant_B' => $contractParticipantB->full_name ?? '',
            'contract_participant_pos_B' => $contractParticipantB->representative_position ?? '',
            'contract_participant_tax_code_B' => $contractParticipantB->tax_code ?? '',
            'contract_participant_address_B' => $contractParticipantB->address ?? '',
            'contract_participant_bank_account_B' => $contractParticipantB->bank_account ?? '',
            'contract_participant_bank_name_B' => $contractParticipantB->bank_name ?? '',
            'contract_participant_phone_B' => $contractParticipantB->phone ?? '',
            'contract_participant_email_B' => $contractParticipantB->email ?? '',

            // Thong tin xu ly
            'contract_department_id' => $this->record->department_id ?? '',
            'contract_status_id' => $this->record->contract_status_id ?? '',
            'contract_sales_employee_id' => $this->record->sales_employee_id ?? '',

            // San pham
            'contract_product_id' => '',
            'contract_product_number' => '',
            'contract_product_total' => '',

            // Thanh toan
            'payment_type_id' => '',
            'payment_received_by_id' => '',
            'payment_date' => '',
            'payment_amount_paid' => '',
            'payment_method' => '',
            'payment_note' => '',
        ];
    }

    // public function ButtonDownload()
    // {
    //     return Action::make('downloadWord')
    //         ->label('Tải hợp đồng')
    //         ->icon('heroicon-o-arrow-down-tray')
    //         ->action('downloadWord')
    //         ->button()
    //         ->render();
    // }

    #[On('downloadWord')]
    public function downloadWord($templateId = null)
    {
        try {
            $contract = $this->record;

            if ($templateId) {
                $template = ExportTemplate::find($templateId);
                $filePath = $template->file_path;
            } else {
                $filePath = 'export-templates/Mau1.docx';
            }

            if (!Storage::disk('public')->exists($filePath)) {
                Notification::make()
                    ->title('Lỗi')
                    ->body('Tệp mẫu không tồn tại.')
                    ->danger()
                    ->send();
                return;
            }

            if (!Storage::disk('public')->exists('temp')) {
                Storage::disk('public')->makeDirectory('temp');
            }

            $newFileName = 'HD_' . $contract->contract_number . '_' . date('YmdHis') . '.docx';
            $newFilePath = 'temp/' . $newFileName;

            Storage::disk('public')->copy($filePath, $newFilePath);
            $fullFilePath = Storage::disk('public')->path($newFilePath);

            $data = $this->prepareContractData($contract);

            $this->fillWordTemplate($fullFilePath, $data);

            // /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            // $disk = Storage::disk('public');
            // $downloadUrl = $disk->url($newFilePath);

            // Notification::make()
            //     ->title('Tạo hợp đồng thành công')
            //     ->success()
            //     ->actions([
            //         \Filament\Notifications\Actions\Action::make('download')
            //             ->label('Tải xuống')
            //             ->url($downloadUrl)
            //             ->openUrlInNewTab(),
            //     ])
            //     ->send();

            // return response()->download($fullFilePath, $newFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Lỗi')
                ->body("Có lỗi xảy ra trong quá trình tải hợp đồng. $e")
                ->danger()
                ->send();
        }
    }

    private function fillWordTemplate(string $filePath, array $data): void
    {
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($filePath);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $templateProcessor->setValue($key, $value);
        }

        // $templateProcessor->setValue('html:contract_terms', $data['contract_terms']);

        if (isset($data['legal_basis']) && is_array($data['legal_basis']) && !empty($data['legal_basis'])) {
            $templateProcessor->cloneBlock('legal_basis', count($data['legal_basis']), true, true);

            foreach ($data['legal_basis'] as $index => $item) {
                $templateProcessor->setValue("legal_basis_item#" . ($index + 1), $item);
            }
        }

        if (isset($data['payment_requirements']) && is_array($data['payment_requirements']) && !empty($data['payment_requirements'])) {
            $templateProcessor->cloneBlock('payment_requirements', count($data['payment_requirements']), true, true);

            foreach ($data['payment_requirements'] as $index => $item) {
                $templateProcessor->setValue("payment_requirements_item#" . ($index + 1), $item);
            }
        }

        if (isset($data['products']) && is_array($data['products'])) {
            $templateProcessor->cloneRow('product_name', count($data['products']));

            foreach ($data['products'] as $index => $product) {
                $rowIndex = $index + 1;
                $templateProcessor->setValue('no#' . $rowIndex, $product['no']);
                $templateProcessor->setValue('product_name#' . $rowIndex, $product['product_name']);
                $templateProcessor->setValue('unit#' . $rowIndex, $product['unit']);
                $templateProcessor->setValue('unit_price#' . $rowIndex, $product['unit_price']);
                $templateProcessor->setValue('qty#' . $rowIndex, $product['qty']);
                $templateProcessor->setValue('total#' . $rowIndex, $product['total']);
            }
        }

        if (!empty($data['contract_terms'])) {
            foreach ($data['contract_terms'] as $i => $term) {
                $index = $i + 1;

                $templateProcessor->setValue("term_type$index", $term['term_type']);

                $templateProcessor->cloneBlock("term_content$index", count($term['content_items']), true, true);

                foreach ($term['content_items'] as $j => $item) {
                    $templateProcessor->setValue("term_content_item{$index}#" . ($j + 1), trim($item));
                }
            }
        }

        $templateProcessor->saveAs($filePath);
    }

    private function prepareContractData(Contract $contract): array
    {
        $participantA = ContractParticipant::where('party_type', 'A')->first();

        $participantB = ContractParticipant::where('contract_id', $contract->id)
            ->where('party_type', 'B')
            ->first();

        $contractProducts = $contract->contractProducts;

        $legalBasisArray = array_filter(array_map('trim', explode(';', $contract->legal_basis)));
        $legalBasis = array_map(function ($item) {
            return $item . ';';
        }, $legalBasisArray);

        $paymentRequirementsArray = array_filter(array_map('trim', explode(';', $contract->payment_requirements)));
        $paymentRequirements = array_map(function ($item) {
            return $item;
        }, $paymentRequirementsArray);

        $data['products'] = [];
        $grandTotal = 0;
        foreach ($contractProducts as $index => $product) {
            $no = $index + 1;
            $data['products'][] = [
                'no' => $no,
                'product_name' => $product->product->name,
                'unit' => $product->product->unit,
                'unit_price' => number_format($product->product->price),
                'qty' => $product->number,
                'total' => number_format($product->total),
            ];

            $grandTotal += $product->total;
        }
        $data['grand_total'] = number_format($grandTotal);
        $data['grand_total_text'] = ucfirst($this->convertNumberToWords($grandTotal));

        $startDateFull = Carbon::parse($contract->sign_date)->format('d \t\h\á\n\g m \n\ă\m Y');
        $startDate = Carbon::parse($contract->start_date)->format('d/m/Y');
        $endDate = Carbon::parse($contract->end_date)->format('d/m/Y');

        $contractTerms = ContractTerm::where('contract_id', $contract->id)
            ->orderBy('order')
            ->get();

        $contractTerms = ContractTerm::where('contract_id', $contract->id)
            ->orderBy('order')
            ->get();

        $formattedTerms = [];
        foreach ($contractTerms as $term) {
            $contentItems = [];
            if (!empty($term->conent)) {
                $contentItems = array_filter(array_map('trim', explode(';', $term->conent)));
            }

            $formattedTerms[] = [
                'term_type' => $term->term_type,
                'content_items' => $contentItems
            ];
        }

        return [
            'contract_type' => mb_strtoupper($contract->contractType->name),
            'contract_number' => $contract->contract_number,
            'contract_purpose' => mb_strtoupper($contract->contract_purpose),
            'contract_purpose_lowercase' => ucfirst(mb_strtolower($contract->contract_purpose)),
            'legal_basis' => $legalBasis,
            'start_date_full' => $startDateFull,
            'start_date' => $startDate,
            'end_date' => $endDate,

            'a_address' => $participantA->address ?? '',
            'a_phone' => $participantA->phone ?? '',
            'a_tax_code' => $participantA->tax_code ?? '',
            'a_representative' => $participantA->full_name ?? '',
            'a_representative_pos' => $participantA->representative_position ?? '',
            'a_bank_name' => $participantA->bank_name ?? '',
            'a_bank_account' => $participantA->bank_account ?? '',

            'b_name' => $participantB->company_name ?? $contract->client->name ?? '',
            'b_address' => $participantB->address ?? $contract->client->address ?? '',
            'b_phone' => $participantB->phone ?? $contract->client->phone ?? '',
            'b_tax_code' => $participantB->tax_code ?? $contract->client->tax_code ?? '',
            'b_representative' => $participantB->full_name ?? '',
            'b_representative_pos' => $participantB->representative_position ?? '',
            'b_bank_name' => $participantB->bank_name ?? '',
            'b_bank_account' => $participantB->bank_account ?? '',

            'products' => $data['products'],
            'grand_total' => number_format($grandTotal),
            'grand_total_text' => ucfirst($this->convertNumberToWords($grandTotal)),

            'contract_form' => $contract->contract_form,
            'payment_terms' => $contract->payment_terms,
            'payment_requirements' => $paymentRequirements,
            'pay_method' => $contract->pay_method === 'transfer' ? 'Chuyển khoản' : 'Tiền mặt',

            'contract_terms' =>  $formattedTerms,
        ];
    }

    private function convertNumberToWords($number): string
    {
        $hyphen = ' ';
        $conjunction = ' ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'không',
            1 => 'một',
            2 => 'hai',
            3 => 'ba',
            4 => 'bốn',
            5 => 'năm',
            6 => 'sáu',
            7 => 'bảy',
            8 => 'tám',
            9 => 'chín',
            10 => 'mười',
            11 => 'mười một',
            12 => 'mười hai',
            13 => 'mười ba',
            14 => 'mười bốn',
            15 => 'mười lăm',
            16 => 'mười sáu',
            17 => 'mười bảy',
            18 => 'mười tám',
            19 => 'mười chín',
            20 => 'hai mươi',
            30 => 'ba mươi',
            40 => 'bốn mươi',
            50 => 'năm mươi',
            60 => 'sáu mươi',
            70 => 'bảy mươi',
            80 => 'tám mươi',
            90 => 'chín mươi',
            100 => 'trăm',
            1000 => 'nghìn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'nghìn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            trigger_error(
                'convertNumberToWords only accepts numbers between -'
                    . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen;
                    if ($units == 1) {
                        $string .= 'mốt';
                    } elseif ($units == 5) {
                        $string .= 'lăm';
                    } else {
                        $string .= $dictionary[$units];
                    }
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convertNumberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return ucfirst($string);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('ContractTabs')
                    ->tabs([
                        Tab::make('Thông tin')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Thông tin chung')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('contract_client_id')
                                                    ->label('Khách hàng')
                                                    ->options(Client::all()->pluck('name', 'id')->toArray())
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $client = Client::find($state);

                                                        $set('contract_client_address', $client?->address);
                                                    })
                                                    ->required(),
                                                Select::make('contract_type_id')
                                                    ->label('Loại hợp đồng')
                                                    ->options(ContractType::all()->pluck('name', 'id')->toArray())
                                                    ->required(),
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_purpose')
                                                    ->label('Mục đích hợp đồng'),
                                                TextInput::make('contract_form')
                                                    ->label('Hình thức hợp đồng'),
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_client_address')
                                                    ->label('Địa chỉ giao hàng')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                            ])
                                            ->columns(1),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_number')
                                                    ->label('Số hợp đồng')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                DatePicker::make('contract_start_date')
                                                    ->label('Ngày hợp đồng có hiệu lực')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $dayNumber = Carbon::parse($state)->diffInDays(Carbon::parse($this->record->end_date));

                                                        $set('contract_day_number', $dayNumber);
                                                    })
                                                    ->required(),
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                DatePicker::make('contract_sign_date')
                                                    ->label('Ngày ký hợp đồng')
                                                    ->required(),
                                                DatePicker::make('contract_end_date')
                                                    ->label('Ngày hợp đồng hết hiệu lực')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $dayNumber = Carbon::parse($this->record->start_date)->diffInDays(Carbon::parse($state));

                                                        $set('contract_day_number', $dayNumber);
                                                        $set('contract_end_date', $state);
                                                    })
                                                    ->required(),
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_day_number')
                                                    ->label('Số ngày thực hiện')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                Select::make('contract_payment_method')
                                                    ->label('Hình thức thanh toán')
                                                    ->options([
                                                        'cash' => 'Tiền mặt',
                                                        'transfer' => 'Chuyển khoản',
                                                        'other' => 'Khác'
                                                    ]),
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_total_value')
                                                    ->label('Giá trị hợp đồng')
                                                    ->numeric()
                                                    ->required()
                                                    ->suffix('VNĐ'),
                                                DatePicker::make('contract_end_date')
                                                    ->label('Thời hạn thanh toán')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                            ]),

                                        Textarea::make('contract_legal_basis')
                                            ->label('Cơ sở pháp lý')
                                            ->placeholder('Căn cứ 1; Căn cứ 2; Căn cứ 3; ...')
                                            ->rows(3),
                                    ]),

                                Section::make('Thông tin Bên A, bên B')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_A')
                                                    ->label('Đại diện bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_B')
                                                    ->label('Đại diện bên B')
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_pos_A')
                                                    ->label('Chức vụ đại diện bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_pos_B')
                                                    ->label('Chức vụ đại diện bên B')
                                            ])
                                            ->columns(2),
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_tax_code_A')
                                                    ->label('Mã số thuế bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_tax_code_B')
                                                    ->label('Mã số thuế bên B')
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_address_A')
                                                    ->label('Địa chỉ đăng ký kinh doanh bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_address_B')
                                                    ->label('Địa chỉ đăng ký kinh doanh bên B')
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_bank_account_A')
                                                    ->label('Tài khoản ngân hàng bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_bank_account_B')
                                                    ->label('Tài khoản ngân hàng bên B')
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_bank_name_A')
                                                    ->label('Ngân hàng bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_bank_name_B')
                                                    ->label('Ngân hàng bên B')
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_phone_A')
                                                    ->label('Số điện thoại bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_phone_B')
                                                    ->label('Số điện thoại bên B')
                                            ])
                                            ->columns(2),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_participant_email_A')
                                                    ->label('Email bên A')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                TextInput::make('contract_participant_email_B')
                                                    ->label('Email bên B')
                                            ])
                                            ->columns(2),
                                    ]),

                                Section::make('Thông tin thanh toán')
                                    ->schema([
                                        Textarea::make('contract_payment_terms')
                                            ->label('Điều khoản thanh toán')
                                            ->placeholder('Khoản 1; Khoản 2; Khoản 3; ...')
                                            ->rows(3)
                                            ->extraAttributes(['class' => 'resize-y']),

                                        Textarea::make('contract_payment_requirements')
                                            ->label('Yêu cầu thanh toán')
                                            ->placeholder('Yêu cầu 1; Yêu cầu 2; Yêu cầu 3; ...')
                                            ->rows(3),

                                        Grid::make()
                                            ->schema([
                                                Select::make('contract_payment_status')
                                                    ->label('Trạng thái thanh toán')
                                                    ->options([
                                                        '1' => 'Đã thanh toán',
                                                        '0' => 'Chưa thanh toán',
                                                    ]),
                                                Select::make('contract_liquidation_status')
                                                    ->label('Trạng thái thanh lý')
                                                    ->options([
                                                        '1' => 'Đã thanh lý',
                                                        '0' => 'Chưa thanh lý',
                                                    ]),
                                            ])
                                            ->columns(2),
                                    ]),

                                Section::make('Thông tin xử lý')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('contract_sales_employee_id')
                                                    ->label('Nhân viên kinh doanh')
                                                    ->required()
                                                    ->options(User::all()->pluck('name', 'id')->toArray()),
                                                Select::make('contract_department_id')
                                                    ->label('Bộ phận kinh doanh')
                                                    ->required()
                                                    ->options(Department::all()->pluck('name', 'id')->toArray()),
                                            ])
                                            ->columns(2),
                                        Grid::make()
                                            ->schema([
                                                Select::make('contract_status_id')
                                                    ->label('Trạng thái xử lý')
                                                    ->required()
                                                    ->options(
                                                        ContractStatus::all()->pluck('name', 'id')->toArray()
                                                    ),
                                            ])
                                            ->columns(1),
                                    ])
                                    ->columns(2),

                                Actions::make([
                                    Actions\Action::make('saveInformation')
                                        ->label('Lưu thông tin')
                                        ->action(
                                            fn(array $data, Set $set, Get $get)
                                            => $this->saveContractInformation($data, $set, $get)
                                        )
                                        ->color('success')
                                        ->icon('heroicon-o-check-circle')
                                ])
                            ]),

                        Tab::make('Điều khoản')
                            ->icon('heroicon-o-scale')
                            ->badge(ContractTerm::where('contract_id', $this->record->id)->count())
                            ->schema([
                                Section::make('Điều khoản hợp đồng')
                                    ->schema([
                                        Section::make('Danh sách điều khoản của hợp đồng')
                                            ->schema([
                                                \Filament\Forms\Components\View::make('components.contract-terms-table')
                                                    ->viewData([
                                                        'contractTerms' => function () {
                                                            return \App\Models\ContractTerm::query()
                                                                ->where('contract_id', $this->record->id)
                                                                ->with(['contract'])
                                                                ->get();
                                                        }
                                                    ]),
                                                View::make('components.buttons.add-export-template-button'),
                                            ]),
                                    ]),

                                Section::make('Thêm điều khoản')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_term_type')
                                                    ->label('Tên điều khoản')
                                            
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_term_order')
                                                    ->label('Thứ tự hiển thị điều khoản')
                                                    ->numeric()
                                                    
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                Textarea::make('contract_term_content')
                                                    ->label('Nội dung điều khoản')
                                                    ->placeholder('Nội dung 1; Nội dung 2; Nội dung 3; ...')
                                                    ->rows(6)
                                                    
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        // \Filament\Forms\Components\View::make('components.buttons.save-contract-product-button'),

                                        Actions::make([
                                            Actions\Action::make('saveTerm')
                                                ->label('Lưu điều khoản')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractTerm($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),

                        Tab::make('Hàng hóa')
                            ->icon('heroicon-o-shopping-bag')
                            ->badge($this->contractProductCount)
                            ->schema([
                                Section::make('Danh sách hàng hóa của hợp đồng')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.contract-products-table')
                                            ->viewData([
                                                'contractProducts' => function () {
                                                    return \App\Models\ContractProduct::query()
                                                        ->where('contract_id', $this->record->id)
                                                        ->with(['contract', 'product'])
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),
                                Section::make('Thêm hàng hóa')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('contract_product_id')
                                                    ->label('Sản phầm')
                                                   
                                                    ->reactive()
                                                    ->options(Product::all()->pluck('name', 'id')->toArray())
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $product = Product::find($state);

                                                        $numberProduct = $get('contract_product_number');
                                                        if (!empty($numberProduct)) {
                                                            $set('contract_product_total', (int)$product->price * (int)$numberProduct);
                                                        }

                                                        $set('contract_product_unit', $product->unit);
                                                        $set('contract_product_price', (int)$product->price);
                                                    })
                                                    ->columnSpan(3),
                                                TextInput::make('contract_product_unit')
                                                    ->label('Đơn vị')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_product_number')
                                                    ->label('Số lượng')
                                                   
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $contractProduct = $get('contract_product_id');
                                                        if (!empty($contractProduct)) {
                                                            $set('contract_product_total', (int)$get('contract_product_price') * (int)$state);
                                                        }
                                                    })
                                                    ->columnSpan(3),
                                                TextInput::make('contract_product_price')
                                                    ->label('Đơn giá')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contract_product_total')
                                                    ->label('Thành tiền')
                                                    ->readOnly(true)
                                                    // ->disabled()
                                                    // ->dehydrated(false)
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        // \Filament\Forms\Components\View::make('components.buttons.save-contract-product-button'),

                                        Actions::make([
                                            Actions\Action::make('saveProduct')
                                                ->label('Lưu hàng hóa')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractProduct($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),

                        Tab::make('Trao đổi')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->badge(\App\Models\ContractCommunication::where('contract_id', $this->record->id)->count())
                            ->schema([
                                Section::make('Lịch sử trao đổi')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.contract-communications-table')
                                            ->viewData([
                                                'contractCommunications' => function () {
                                                    return \App\Models\ContractCommunication::query()
                                                        ->where('contract_id', $this->record->id)
                                                        ->with(['contract', 'creator'])
                                                        ->orderBy('date', 'desc')
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),

                                Section::make('Thêm trao đổi')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                DatePicker::make('communication_date')
                                                    ->label('Ngày trao đổi')
                                                    ->required()
                                                    ->columnSpan(3),
                                                TextInput::make('communication_person')
                                                    ->label('Người trao đổi')
                                                    ->required()
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                RichEditor::make('communication_content')
                                                    ->label('Nội dung')
                                                    ->required()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                FileUpload::make('communication_attachments')
                                                    ->label('Tài liệu đính kèm')
                                                    ->multiple()
                                                    ->directory('contract-communications')
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Actions::make([
                                            Actions\Action::make('saveCommunication')
                                                ->label('Lưu trao đổi')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractCommunication($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),

                        Tab::make('Ghi chú')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->badge(\App\Models\ContractNote::where('contract_id', $this->record->id)->count())
                            ->schema([
                                Section::make('Danh sách ghi chú')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.contract-notes-table')
                                            ->viewData([
                                                'contractNotes' => function () {
                                                    return \App\Models\ContractNote::query()
                                                        ->where('contract_id', $this->record->id)
                                                        ->with(['contract'])
                                                        ->orderBy('created_at', 'desc')
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),

                                Section::make('Thêm ghi chú')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('note_title')
                                                    ->label('Tiêu đề ghi chú')
                                                    ->required()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                RichEditor::make('note_content')
                                                    ->label('Nội dung ghi chú')
                                                    ->required()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Actions::make([
                                            Actions\Action::make('saveNote')
                                                ->label('Lưu ghi chú')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractNote($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),

                        Tab::make('Thanh toán')
                            ->icon('heroicon-o-banknotes')
                            ->badge(Payment::where('contract_id', $this->record->id)->count())
                            ->schema([
                                Section::make('Danh sách thanh toán của hợp đồng')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.contract-payments-table')
                                            ->viewData([
                                                'contractPayments' => function () {
                                                    return \App\Models\Payment::query()
                                                        ->where('contract_id', $this->record->id)
                                                        ->with(['contract', 'paymentType', 'user'])
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),
                                Section::make('Thêm thanh toán')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('payment_type_id')
                                                    ->label('Loại thanh toán')
                                                   
                                                    ->options(PaymentType::all()->pluck('name', 'id')->toArray())
                                                    ->columnSpan(3),
                                                Select::make('payment_received_by_id')
                                                    ->label('Nhân viên thu')
                                                    
                                                    ->options(User::all()->pluck('name', 'id')->toArray())
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                DatePicker::make('payment_date')
                                                    ->label('Ngày thanh toán')
                                                   
                                                    ->columnSpan(3),
                                                TextInput::make('payment_amount_paid')
                                                    ->label('Giá trị thanh toán')
                                                  
                                                    ->numeric()
                                                    ->suffix('VNĐ')
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                Select::make('payment_method')
                                                    ->label('Phương thức thanh toán')
                                                   
                                                    ->options([
                                                        'pay' => 'Chuyển khoản',
                                                        'cash' => 'Tiền mặt',
                                                    ])
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                Textarea::make('payment_note')
                                                    ->label('Nội dung thanh toán')
                                                    
                                                    ->rows(3)
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        // \Filament\Forms\Components\View::make('components.buttons.save-contract-payment-button'),

                                        Actions::make([
                                            Actions\Action::make('savePayment')
                                                ->label('Lưu thanh toán')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractPayment($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),

                        Tab::make('Hóa đơn')
                            ->icon('heroicon-o-document-text')
                            ->badge(\App\Models\Invoice::where('contract_id', $this->record->id)->count())
                            ->schema([
                                Section::make('Danh sách hóa đơn')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.contract-invoices-table')
                                            ->viewData([
                                                'contractInvoices' => function () {
                                                    return \App\Models\Invoice::query()
                                                        ->where('contract_id', $this->record->id)
                                                        ->with(['contract'])
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),

                                Section::make('Thêm hóa đơn')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('invoice_number')
                                                    ->label('Số hóa đơn')
                                                    ->required()
                                                    ->columnSpan(2),
                                                DatePicker::make('invoice_date')
                                                    ->label('Ngày hóa đơn')
                                                    ->required()
                                                    ->columnSpan(2),
                                                DatePicker::make('invoice_due_date')
                                                    ->label('Hạn thanh toán')
                                                    ->required()
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('invoice_amount')
                                                    ->label('Số tiền')
                                                    ->numeric()
                                                    ->required()
                                                    ->suffix('VNĐ')
                                                    ->columnSpan(3),
                                                Select::make('invoice_status')
                                                    ->label('Trạng thái')
                                                    ->options([
                                                        'unpaid' => 'Chưa thanh toán',
                                                        'partial' => 'Thanh toán một phần',
                                                        'paid' => 'Đã thanh toán',
                                                    ])
                                                    ->required()
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                FileUpload::make('invoice_file')
                                                    ->label('File hóa đơn')
                                                    ->directory('contract-invoices')
                                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                Textarea::make('invoice_note')
                                                    ->label('Ghi chú')
                                                    ->rows(3)
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Actions::make([
                                            Actions\Action::make('saveInvoice')
                                                ->label('Lưu hóa đơn')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractInvoice($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),

                        Tab::make('Tài liệu')
                            ->icon('heroicon-o-folder')
                            ->badge(\App\Models\ContractAttachment::where('contract_id', $this->record->id)->count())
                            ->schema([
                                Section::make('Danh sách tài liệu đính kèm')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.contract-attachments-table')
                                            ->viewData([
                                                'contractAttachments' => function () {
                                                    return \App\Models\ContractAttachment::query()
                                                        ->where('contract_id', $this->record->id)
                                                        ->with(['contract'])
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),

                                Section::make('Thêm tài liệu đính kèm')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('attachment_title')
                                                    ->label('Tên tài liệu')
                                                    ->required()
                                                    ->columnSpan(3),
                                                Select::make('attachment_type')
                                                    ->label('Loại tài liệu')
                                                    ->options([
                                                        'contract' => 'Hợp đồng chính',
                                                        'appendix' => 'Phụ lục',
                                                        'annex' => 'Bản đính kèm',
                                                        'report' => 'Báo cáo',
                                                        'other' => 'Khác',
                                                    ])
                                                    ->required()
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                FileUpload::make('attachment_file')
                                                    ->label('File tài liệu')
                                                    ->required()
                                                    ->directory('contract-attachments')
                                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                                    ->helperText('Chấp nhận file PDF, hình ảnh và file Word')
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                Textarea::make('attachment_description')
                                                    ->label('Mô tả')
                                                    ->rows(3)
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Actions::make([
                                            Actions\Action::make('saveAttachment')
                                                ->label('Lưu tài liệu')
                                                ->action(
                                                    fn(array $data, Set $set, Get $get)
                                                    => $this->saveContractAttachment($data, $set, $get)
                                                )
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                        ])
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function getUser($role_code): ?\App\Models\User
    {
        return $this->record
            ->contractType
            ->supervisors()
            ->whereHas('role', function ($query) use ($role_code) {
                $query->where('code', $role_code);
            })
            ->with('user')
            ->first()?->user;
    }

    protected function generateAvatarHtml(array $user): string
    {
        if (!empty($user['avatar']) && Storage::disk('public')->exists($user['avatar'])) {
            $avatarUrl = Storage::url($user['avatar']);
            return '<div class="h-10 w-10 rounded-full overflow-hidden">
                    <img src="' . $avatarUrl . '" alt="' . $user['name'] . '" class="h-full w-full object-cover">
                </div>';
        }

        return '<div class="h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center">
                <span class="text-gray-300">' . substr($user['name'], 0, 1) . '</span>
            </div>';
    }

    public function SupervisorTab(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                \Filament\Infolists\Components\Section::make()
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('responsible_persons_heading')
                            ->label('Người phụ trách (5)')
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->columnSpanFull(),

                        \Filament\Infolists\Components\TextEntry::make('responsible_persons')
                            ->hiddenLabel()
                            ->html()
                            ->getStateUsing(function () {
                                $users = [
                                    [
                                        'name' => $this->record->salesEmployee->name  ?? 'Chưa lập',
                                        'role' => 'Kinh doanh',
                                        'email' => $this->record->salesEmployee->email ?? '',
                                        'avatar' => $this->record->salesEmployee->avatar ?? ''
                                    ],
                                    [
                                        'name' => $this->getUser('quanly')->name ?? 'Chưa lập',
                                        'role' => 'Quản lý',
                                        'email' => $this->getUser('quanly')->email ?? '',
                                        'avatar' => $this->getUser('quanly')->avatar ?? ''
                                    ],
                                    [
                                        'name' => $this->getUser('hopdong')->name ?? 'Chưa lập',
                                        'role' => 'Hợp đồng',
                                        'email' => $this->getUser('hopdong')->email ?? '',
                                        'avatar' => $this->getUser('hopdong')->avatar ?? ''
                                    ],
                                    [
                                        'name' => $this->getUser('ketoan')->name ?? 'Chưa lập',
                                        'role' => 'Kế toán',
                                        'email' => $this->getUser('ketoan')->email ?? '',
                                        'avatar' => $this->getUser('ketoan')->avatar ?? ''
                                    ],
                                    [
                                        'name' => $this->getUser('trienkhai')->name ?? 'Chưa lập',
                                        'role' => 'Triển khai',
                                        'email' => $this->getUser('trienkhai')->email ?? '',
                                        'avatar' => $this->getUser('trienkhai')->avatar ?? ''
                                    ],
                                    [
                                        'name' => $this->getUser('theodoi')->name ?? 'Chưa lập',
                                        'role' => 'Theo dõi',
                                        'email' => $this->getUser('theodoi')->email ?? '',
                                        'avatar' => $this->getUser('theodoi')->avatar ?? ''
                                    ]
                                ];

                                $html = '';
                                foreach ($users as $user) {
                                    $avatarHtml = $this->generateAvatarHtml($user);
                                    $html .= '
                                <div class="flex items-center gap-4 py-3 border-b border-gray-700">
                                    ' . $avatarHtml . '
                                    <div>
                                        <div class="font-medium text-white">' . $user['name'] . '</div>
                                        <div class="text-gray-400 text-sm">' . $user['role'] . '</div>
                                        <div class="text-gray-500 text-xs">' . $user['email'] . '</div>
                                    </div>
                                </div>';
                                }

                                return $html;
                            })
                            ->columnSpanFull()
                    ])
            ]);
    }

    public function contractInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                InfolistSection::make()
                    ->schema([
                        \Filament\Infolists\Components\Actions::make([
                            \Filament\Infolists\Components\Actions\Action::make('pending')
                                ->label('Chờ duyệt')
                                ->icon('heroicon-o-check-circle')
                                ->button()
                                ->color(fn() => $this->record->contractStatus?->code === 'choduyet' ? 'primary' : 'gray')
                                ->extraAttributes(['class' => 'flex-grow min-w-[100px] text-center'])
                                ->action(function () {
                                    $draftStatusId = ContractStatus::where('code', 'choduyet')->first()?->id;
                                    if ($draftStatusId) {
                                        $this->record->contract_status_id = $draftStatusId;
                                        $this->record->save();

                                        $this->dispatch('refresh');

                                        Notification::make()
                                            ->title('Hợp đồng đã được chuyển sang trạng thái dự thảo')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Không tìm thấy trạng thái dự thảo')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            \Filament\Infolists\Components\Actions\Action::make('approved')
                                ->label('Đã duyệt')
                                ->icon('heroicon-o-check-badge')
                                ->button()
                                ->color(fn() => $this->record->contractStatus?->code === 'daduyet' ? 'primary' : 'gray')
                                ->extraAttributes(['class' => 'flex-grow min-w-[100px] text-center'])
                                ->action(function () {
                                    $draftStatusId = ContractStatus::where('code', 'daduyet')->first()?->id;
                                    if ($draftStatusId) {
                                        $this->record->contract_status_id = $draftStatusId;
                                        $this->record->save();

                                        $this->dispatch('refresh');

                                        Notification::make()
                                            ->title('Hợp đồng đã được chuyển sang trạng thái dự thảo')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Không tìm thấy trạng thái dự thảo')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            \Filament\Infolists\Components\Actions\Action::make('draft')
                                ->label('Dự thảo')
                                ->icon('heroicon-o-document-text')
                                ->button()
                                ->color(fn() => $this->record->contractStatus?->code === 'duthao' ? 'primary' : 'gray')
                                ->extraAttributes(['class' => 'flex-grow min-w-[100px] text-center'])
                                ->action(function () {
                                    $draftStatusId = ContractStatus::where('code', 'duthao')->first()?->id;
                                    if ($draftStatusId) {
                                        $this->record->contract_status_id = $draftStatusId;
                                        $this->record->save();

                                        $this->dispatch('refresh');

                                        Notification::make()
                                            ->title('Hợp đồng đã được chuyển sang trạng thái dự thảo')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Không tìm thấy trạng thái dự thảo')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            \Filament\Infolists\Components\Actions\Action::make('negotiating')
                                ->label('Thương thảo')
                                ->icon('heroicon-o-chat-bubble-left-right')
                                ->button()
                                ->color(fn() => $this->record->contractStatus?->code === 'thuongthao' ? 'primary' : 'gray')
                                ->extraAttributes(['class' => 'flex-grow min-w-[100px] text-center'])
                                ->action(function () {
                                    $draftStatusId = ContractStatus::where('code', 'thuongthao')->first()?->id;
                                    if ($draftStatusId) {
                                        $this->record->contract_status_id = $draftStatusId;
                                        $this->record->save();

                                        $this->dispatch('refresh');

                                        Notification::make()
                                            ->title('Hợp đồng đã được chuyển sang trạng thái dự thảo')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Không tìm thấy trạng thái dự thảo')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            \Filament\Infolists\Components\Actions\Action::make('signing')
                                ->label('Trình ký')
                                ->icon('heroicon-o-pencil-square')
                                ->button()
                                ->color(fn() => $this->record->contractStatus?->code === 'trinhky' ? 'primary' : 'gray')
                                ->extraAttributes(['class' => 'flex-grow min-w-[100px] text-center'])
                                ->action(function () {
                                    $draftStatusId = ContractStatus::where('code', 'trinhky')->first()?->id;
                                    if ($draftStatusId) {
                                        $this->record->contract_status_id = $draftStatusId;
                                        $this->record->save();

                                        $this->dispatch('refresh');

                                        Notification::make()
                                            ->title('Hợp đồng đã được chuyển sang trạng thái dự thảo')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Không tìm thấy trạng thái dự thảo')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            \Filament\Infolists\Components\Actions\Action::make('signed')
                                ->label('Đã ký')
                                ->icon('heroicon-o-document-check')
                                ->button()
                                ->color(fn() => $this->record->contractStatus?->code === 'daky' ? 'primary' : 'gray')
                                ->extraAttributes(['class' => 'flex-grow min-w-[100px] text-center'])
                                ->action(function () {
                                    $draftStatusId = ContractStatus::where('code', 'daky')->first()?->id;
                                    if ($draftStatusId) {
                                        $this->record->contract_status_id = $draftStatusId;
                                        $this->record->save();

                                        $this->dispatch('refresh');

                                        Notification::make()
                                            ->title('Hợp đồng đã được chuyển sang trạng thái dự thảo')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Không tìm thấy trạng thái dự thảo')
                                            ->danger()
                                            ->send();
                                    }
                                }),
                        ])
                            ->extraAttributes([
                                'class' => 'bg-[#111827] rounded-md text-white py-2 flex justify-between items-center w-full gap-2',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
            ]);
    }

    // Custom actions for the contract
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('approve')
                ->label('Duyệt')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    $this->record->status = 'approved';
                    $this->record->save();

                    \Filament\Notifications\Notification::make()
                        ->title('Hợp đồng đã được duyệt')
                        ->success()
                        ->send();

                    // $this->redirect(static::getUrl('detail', ['record' => $this->record]));
                })
                ->visible(fn() => $this->record->status === 'pending'),

            \Filament\Actions\Action::make('decline')
                ->label('Từ chối')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->status = 'draft';
                    $this->record->save();

                    \Filament\Notifications\Notification::make()
                        ->title('Hợp đồng bị từ chối duyệt')
                        ->danger()
                        ->send();

                    // $this->redirect(static::getUrl('detail', ['record' => $this->record]));
                })
                ->visible(fn() => $this->record->status === 'pending'),

            \Filament\Actions\Action::make('submit')
                ->label('Trình duyệt')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->action(function () {
                    $this->record->status = 'pending';
                    $this->record->save();

                    \Filament\Notifications\Notification::make()
                        ->title('Hợp đồng đã được trình duyệt')
                        ->success()
                        ->send();

                    // $this->redirect(static::getUrl('detail', ['record' => $this->record]));
                })
                ->visible(fn() => in_array($this->record->status, ['draft', 'negotiating'])),

            \Filament\Actions\Action::make('sign')
                ->label('Ký hợp đồng')
                ->icon('heroicon-o-pencil')
                ->color('success')
                ->action(function () {
                    $this->record->status = 'signed';
                    $this->record->save();

                    \Filament\Notifications\Notification::make()
                        ->title('Hợp đồng đã được ký')
                        ->success()
                        ->send();

                    // $this->redirect(static::getUrl('detail', ['record' => $this->record]));
                })
                ->visible(fn() => $this->record->status === 'signing'),
        ];
    }

    public function saveContractProduct(array $data, Set $set, Get $get)
    {
        $productCheck = [
            'hàng hóa' => $get('contract_product_id'),
            'số lượng' => $get('contract_product_number'),
        ];

        $hasErrors = false;
        foreach ($productCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        $productId = $get('contract_product_id');
        $productNumber = $get('contract_product_number');
        $productTotal = $get('contract_product_total');

        $contractProduct = ContractProduct::where('contract_id', $this->record->id)
            ->where('product_id', $productId)
            ->first();

        if ($contractProduct) {
            $contractProduct->number = $productNumber + $contractProduct->number;
            $contractProduct->total = $productTotal + $contractProduct->total;
            $contractProduct->save();

            Notification::make()
                ->title('Thêm sản phẩm thành công')
                ->success()
                ->send();
            $this->dispatch('refresh');

            return;
        }

        ContractProduct::create([
            'contract_id' => $this->record->id,
            'product_id' => $productId,
            'number' => $productNumber,
            'total' => $productTotal,
        ]);

        // $this->contractProductCount = ContractProduct::where('contract_id', $this->record->id)->count();

        Notification::make()
            ->title('Thêm sản phẩm thành công')
            ->success()
            ->send();

        $this->form->fill([
            'contract_product_id' => null,
            'contract_product_unit' => null,
            'contract_product_number' => null,
            'contract_product_price' => null,
            'contract_product_total' => null,
        ]);

        $this->dispatch('refresh');
    }

    #[On('deleteContractProductConfirmed')]
    public function deleteContractProductConfirmed($id): void
    {
        $product = ContractProduct::find($id);

        if (!$product) {
            Notification::make()
                ->title('Không tìm thấy hàng hóa')
                ->danger()
                ->send();
            return;
        }

        $product->delete();

        Notification::make()
            ->title('Xóa hàng hóa thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    public function saveContractPayment(array $data, Set $set, Get $get)
    {
        $paymentCheck = [
            'loại thanh toán' => $get('payment_type_id'),
            'nhân viên thu' => $get('payment_received_by_id'),
            'ngày thanh toán' => $get('payment_date'),
            'số tiền thanh toán' => $get('payment_amount_paid'),
            'phương thức thanh toán' => $get('payment_method'),
            'nội dung thanh toán' => $get('payment_note'),
        ];

        $hasErrors = false;
        foreach ($paymentCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        Payment::create([
            'contract_id' => $this->record->id,
            'payment_type_id' => $get('payment_type_id'),
            'received_by' => $get('payment_received_by_id'),
            'payment_date' => $get('payment_date'),
            'amount_paid' => $get('payment_amount_paid'),
            'method' => $get('payment_method'),
            'note' => $get('payment_note'),
        ]);

        Notification::make()
            ->title('Thêm thanh toán thành công')
            ->success()
            ->send();

        $this->form->fill([
            'payment_type_id' => null,
            'payment_received_by_id' => null,
            'payment_date' => null,
            'payment_amount_paid' => null,
            'payment_method' => null,
            'payment_note' => null,
        ]);

        $this->dispatch('refresh');
    }

    #[On('deleteContractPaymentConfirmed')]
    public function deleteContractPaymentConfirmed($id): void
    {
        $payment = Payment::find($id);

        if (!$payment) {
            Notification::make()
                ->title('Không tìm thấy thanh toán')
                ->danger()
                ->send();
            return;
        }

        $payment->delete();

        Notification::make()
            ->title('Xóa thanh toán thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    public function saveContractInformation(array $data, Set $set, Get $get)
    {
        // Chung
        $clientId = $get('contract_client_id');
        $contractTypeId = $get('contract_type_id');
        $contractPurpose = $get('contract_purpose');
        $contractForm = $get('contract_form');
        $contractStartDate = $get('contract_start_date');
        $contractSignDate = $get('contract_sign_date');
        $contractEndDate = $get('contract_end_date');
        $contractPayMethod = $get('contract_payment_method');
        $contractTotalValue = $get('contract_total_value');
        $contractLegalBasis = $get('contract_legal_basis');

        // Ben B
        $contractParticipantBName = $get('contract_participant_B');
        $contractParticipantPosB = $get('contract_participant_pos_B');
        $contractParticipantTaxCodeB = $get('contract_participant_tax_code_B');
        $contractParticipantAddressB = $get('contract_participant_address_B');
        $contractParticipantBankAccountB = $get('contract_participant_bank_account_B');
        $contractParticipantBankNameB = $get('contract_participant_bank_name_B');
        $contractParticipantPhoneB = $get('contract_participant_phone_B');
        $contractParticipantEmailB = $get('contract_participant_email_B');

        // Thanh toan
        $contractPaymentTerms = $get('contract_payment_terms');
        $contractPaymentRequirements = $get('contract_payment_requirements');
        $contractPaymentStatus = $get('contract_payment_status');
        $contractLiquidationStatus = $get('contract_liquidation_status');

        // Xu ly
        $salesEmployeeId = $get('contract_sales_employee_id');
        $contractDepartmentId = $get('contract_department_id');
        $contractStatusId = $get('contract_status_id');

        $informationCheck = [
            'khách hàng' => $clientId,
            'loại hợp đồng' => $contractTypeId,
            'ngày hợp đồng có hiệu lực' => $contractStartDate,
            'ngày ký hợp đồng' => $contractSignDate,
            'ngày hợp đồng hết hiệu lực' => $contractEndDate,
            'giá trị hợp đồng' => $contractTotalValue,
            'nhân viên kinh doanh' => $salesEmployeeId,
            'bộ phận kinh doanh' => $contractDepartmentId,
            'trạng thái xử lý' => $contractStatusId
        ];

        $hasErrors = false;
        foreach ($informationCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        $contract = Contract::find($this->record->id);

        $contract->client_id = $clientId;
        $contract->contract_type_id = $contractTypeId;
        $contract->contract_purpose = $contractPurpose;
        $contract->contract_form = $contractForm;
        $contract->start_date = $contractStartDate;
        $contract->sign_date = $contractSignDate;
        $contract->end_date = $contractEndDate;
        $contract->pay_method = $contractPayMethod;
        $contract->total_value = $contractTotalValue;
        $contract->legal_basis = $contractLegalBasis;
        $contract->payment_terms = $contractPaymentTerms;
        $contract->payment_requirements = $contractPaymentRequirements;
        $contract->sales_employee_id = $salesEmployeeId;
        $contract->department_id = $contractDepartmentId;
        $contract->contract_status_id = $contractStatusId;
        $contract->pay = $contractPaymentStatus;
        $contract->liquidation = $contractLiquidationStatus;
        $contract->save();

        // Ben A, Ben B
        $contractParticipantB = ContractParticipant::where('contract_id', $contract->id)
            ->where('party_type', 'B')
            ->first();
        if ($contractParticipantB) {
            $contractParticipantB->full_name = $contractParticipantBName;
            $contractParticipantB->representative_position = $contractParticipantPosB;
            $contractParticipantB->tax_code = $contractParticipantTaxCodeB;
            $contractParticipantB->address = $contractParticipantAddressB;
            $contractParticipantB->bank_account = $contractParticipantBankAccountB;
            $contractParticipantB->bank_name = $contractParticipantBankNameB;
            $contractParticipantB->phone = $contractParticipantPhoneB;
            $contractParticipantB->email = $contractParticipantEmailB;
            $contractParticipantB->save();


            Notification::make()
                ->title('Cập nhật thông tin thành công')
                ->success()
                ->send();

            $this->dispatch('refresh');

            return;
        }


        ContractParticipant::create([
            'contract_id' => $contract->id,
            'party_type' => 'B',
            'full_name' => $contractParticipantBName,
            'representative_position' => $contractParticipantPosB,
            'tax_code' => $contractParticipantTaxCodeB,
            'address' => $contractParticipantAddressB,
            'bank_account' => $contractParticipantBankAccountB,
            'bank_name' => $contractParticipantBankNameB,
            'phone' => $contractParticipantPhoneB,
            'email' => $contractParticipantEmailB,
        ]);

        Notification::make()
            ->title('Cập nhật thông tin thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    public function saveContractTerm(array $data, Set $set, Get $get)
    {
        $termType = $get('contract_term_type');
        $termOrder = $get('contract_term_order');
        $termContent = $get('contract_term_content');

        $termCheck = [
            'tên điều khoản' => $termType,
            'thứ tự hiển thị' => $termOrder,
            'nội dung điều khoản' => $termContent,
        ];

        $hasErrors = false;
        foreach ($termCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        $contractTerm = ContractTerm::where('contract_id', $this->record->id)
            ->where('order', $termOrder)
            ->first();

        if ($contractTerm) {

            Notification::make()
                ->title('Vui lòng chọn thứ tự hiển thị khác')
                ->danger()
                ->send();

            $this->dispatch('refresh');

            return;
        }

        ContractTerm::create([
            'contract_id' => $this->record->id,
            'term_type' => $termType,
            'conent' => $termContent,
            'order' => $termOrder,
        ]);

        Notification::make()
            ->title('Thêm điều khoản thành công')
            ->success()
            ->send();

        $set('contract_term_type', '');
        $set('contract_term_order', '');
        $set('contract_term_content', '');

        $this->dispatch('refresh');
    }

    #[On('deleteContractTermConfirmed')]
    public function deleteContractTermConfirmed($id): void
    {
        $contractTerm = ContractTerm::find($id);

        if (!$contractTerm) {
            Notification::make()
                ->title('Không tìm thấy điều khoản')
                ->danger()
                ->send();
            return;
        }

        $contractTerm->delete();

        Notification::make()
            ->title('Xóa điều khoản thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    public function saveContractAttachment(array $data, Set $set, Get $get)
    {
        // Kiểm tra thông tin cơ bản trước (trừ file)
        $basicCheck = [
            'tên tài liệu' => $get('attachment_title'),
            'loại tài liệu' => $get('attachment_type'),
        ];

        $hasErrors = false;
        foreach ($basicCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        // Xử lý file path - debug để xem structure
        $fileData = $get('attachment_file');
        
        // Debug - log để xem cấu trúc data
        \Illuminate\Support\Facades\Log::info('File data:', ['file' => $fileData]);
        
        $filePath = null;
        
        // Xử lý các trường hợp khác nhau của FileUpload
        if (is_array($fileData)) {
            $filePath = $fileData[0] ?? null;
        } elseif (is_string($fileData)) {
            $filePath = $fileData;
        }

        // Nếu vẫn null, thử lấy từ state khác
        if (empty($filePath)) {
            // Thử lấy từ form state
            $formState = $this->form->getState();
            if (isset($formState['attachment_file'])) {
                $fileFromState = $formState['attachment_file'];
                if (is_array($fileFromState)) {
                    $filePath = $fileFromState[0] ?? null;
                } else {
                    $filePath = $fileFromState;
                }
            }
        }

        if (empty($filePath)) {
            Notification::make()
                ->title('Vui lòng chọn file tài liệu')
                ->body('File data: ' . json_encode($fileData)) // Thêm debug info
                ->danger()
                ->send();
            return;
        }

        try {
            \App\Models\ContractAttachment::create([
                'contract_id' => $this->record->id,
                'title' => $get('attachment_title'),
                'type' => $get('attachment_type'),
                'file_path' => $filePath,
                'file_name' => is_string($filePath) ? basename($filePath) : 'unknown',
                'description' => $get('attachment_description') ?? '',
                'uploaded_at' => now(),
                'uploaded_by' => (int) (auth()->id() ?? 1),
            ]);

            Notification::make()
                ->title('Thêm tài liệu thành công')
                ->success()
                ->send();

            $this->form->fill([
                'attachment_title' => null,
                'attachment_type' => null,
                'attachment_file' => null,
                'attachment_description' => null,
            ]);

            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Lỗi khi lưu tài liệu')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    #[On('deleteContractAttachmentConfirmed')]
    public function deleteContractAttachmentConfirmed($id): void
    {
        $attachment = \App\Models\ContractAttachment::find($id);

        if (!$attachment) {
            Notification::make()
                ->title('Không tìm thấy tài liệu')
                ->danger()
                ->send();
            return;
        }

        // Xóa file vật lý nếu tồn tại
        if (!empty($attachment->file_path) && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        Notification::make()
            ->title('Xóa tài liệu thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    // Functions cho tab Hóa đơn
    public function saveContractInvoice(array $data, Set $set, Get $get)
    {
        $invoiceCheck = [
            'số hóa đơn' => $get('invoice_number'),
            'ngày hóa đơn' => $get('invoice_date'),
            'hạn thanh toán' => $get('invoice_due_date'),
            'số tiền' => $get('invoice_amount'),
            'trạng thái' => $get('invoice_status'),
        ];

        $hasErrors = false;
        foreach ($invoiceCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        $fileData = $get('invoice_file');
        $filePath = null;
        if (is_array($fileData)) {
            $filePath = $fileData[0] ?? null;
        } elseif (is_string($fileData)) {
            $filePath = $fileData;
        }

        $invoiceData = [
            'contract_id' => $this->record->id,
            'invoice_number' => $get('invoice_number'),
            'issue_date' => $get('invoice_date'),
            'due_date' => $get('invoice_due_date'),
            'amount' => $get('invoice_amount'),
            'status' => $get('invoice_status'),
            'note' => $get('invoice_note') ?? '',
            'created_by' => (int) (auth()->id() ?? 1),
        ];

        // Thêm file_path nếu có
        if (!empty($filePath)) {
            $invoiceData['file_path'] = $filePath;
        }

        try {
            \App\Models\Invoice::create($invoiceData);

            Notification::make()
                ->title('Thêm hóa đơn thành công')
                ->success()
                ->send();

            $this->form->fill([
                'invoice_number' => null,
                'invoice_date' => null,
                'invoice_due_date' => null,
                'invoice_amount' => null,
                'invoice_status' => null,
                'invoice_file' => null,
                'invoice_note' => null,
            ]);

            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Lỗi khi lưu hóa đơn')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    #[On('deleteContractInvoiceConfirmed')]
    public function deleteContractInvoiceConfirmed($id): void
    {
        $invoice = \App\Models\Invoice::find($id);

        if (!$invoice) {
            Notification::make()
                ->title('Không tìm thấy hóa đơn')
                ->danger()
                ->send();
            return;
        }

        if (!empty($invoice->file_path) && Storage::disk('public')->exists($invoice->file_path)) {
            Storage::disk('public')->delete($invoice->file_path);
        }

        $invoice->delete();

        Notification::make()
            ->title('Xóa hóa đơn thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    // Functions cho tab Ghi chú
    public function saveContractNote(array $data, Set $set, Get $get)
    {
        $noteCheck = [
            'tiêu đề ghi chú' => $get('note_title'),
            'nội dung ghi chú' => $get('note_content'),
        ];

        $hasErrors = false;
        foreach ($noteCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        try {
            \App\Models\ContractNote::create([
                'contract_id' => $this->record->id,
                'title' => $get('note_title'),
                'content' => $get('note_content'),
                'created_by' => (int) (auth()->id() ?? 1),
            ]);

            Notification::make()
                ->title('Thêm ghi chú thành công')
                ->success()
                ->send();

            $this->form->fill([
                'note_title' => null,
                'note_content' => null,
            ]);

            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Lỗi khi lưu ghi chú')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    #[On('deleteContractNoteConfirmed')]
    public function deleteContractNoteConfirmed($id): void
    {
        $note = \App\Models\ContractNote::find($id);

        if (!$note) {
            Notification::make()
                ->title('Không tìm thấy ghi chú')
                ->danger()
                ->send();
            return;
        }

        $note->delete();

        Notification::make()
            ->title('Xóa ghi chú thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    // Functions cho tab Trao đổi  
    public function saveContractCommunication(array $data, Set $set, Get $get)
    {
        $communicationCheck = [
            'ngày trao đổi' => $get('communication_date'),
            'người trao đổi' => $get('communication_person'),
            'nội dung' => $get('communication_content'),
        ];

        $hasErrors = false;
        foreach ($communicationCheck as $field => $value) {
            if (empty($value)) {
                Notification::make()
                    ->title("Vui lòng điền đầy đủ thông tin {$field}")
                    ->danger()
                    ->send();
                $hasErrors = true;
                break;
            }
        }

        if ($hasErrors) {
            return;
        }

        $fileData = $get('communication_attachments');
        $filePaths = [];
        if (is_array($fileData)) {
            $filePaths = $fileData;
        }

        try {
            \App\Models\ContractCommunication::create([
                'contract_id' => $this->record->id,
                'date' => $get('communication_date'),
                'person' => $get('communication_person'),
                'content' => $get('communication_content'),
                'attachments' => $filePaths, // Laravel sẽ tự động convert array thành JSON
                'created_by' => (int) (auth()->id() ?? 1),
            ]);

            Notification::make()
                ->title('Thêm trao đổi thành công')
                ->success()
                ->send();

            $this->form->fill([
                'communication_date' => null,
                'communication_person' => null,
                'communication_content' => null,
                'communication_attachments' => null,
            ]);

            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Lỗi khi lưu trao đổi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    #[On('deleteContractCommunicationConfirmed')]
    public function deleteContractCommunicationConfirmed($id): void
    {
        $communication = \App\Models\ContractCommunication::find($id);

        if (!$communication) {
            Notification::make()
                ->title('Không tìm thấy trao đổi')
                ->danger()
                ->send();
            return;
        }

        // Xóa files đính kèm nếu có
        if (!empty($communication->attachments) && is_array($communication->attachments)) {
            foreach ($communication->attachments as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        $communication->delete();

        Notification::make()
            ->title('Xóa trao đổi thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }
}
