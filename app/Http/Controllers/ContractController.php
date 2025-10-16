<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ContractController extends Controller
{
    public function exportWord($id)
    {
        $contract = Contract::findOrFail($id);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText("HỢP ĐỒNG", ['bold' => true, 'size' => 16]);
        $section->addTextBreak(2);

        $section->addText("Start Date: " . $contract->start_date);
        $section->addText("End Date: " . $contract->end_date);
        $section->addText("Total Value: " . number_format($contract->total_value, 2) . " EUR");
        $section->addText("Status: " . ucfirst($contract->status));
        $section->addText("Description: " . $contract->description);

        $fileName = "contract_{$contract->id}.docx";
        $filePath = storage_path($fileName);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
