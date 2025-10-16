<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Contract;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractWordExportController extends Controller
{
    public function exportWord($id)
    {
        $contract = Contract::with('client', 'contractType')->findOrFail($id);

        $template = new TemplateProcessor(public_path('templates/contract_template.docx'));
        $template->setValue('contract_number', $contract->contract_number);
        $template->setValue('client_id', $contract->client->name);
        $template->setValue('contract_type_id', $contract->contractType->name);
        $template->setValue('start_date', $contract->start_date);
        $template->setValue('end_date', $contract->end_date);
        $template->setValue('total_value', number_format($contract->total_value) . " VND");
        $template->setValue('status', $contract->status);
        $template->setValue('description', $contract->description);
        $template->setValue('created_at', $contract->created_at);
        $template->setValue('updated_at', $contract->updated_at);

        $fileName = "contract_{$contract->contract_number}.docx";

        return new StreamedResponse(function () use ($template, $fileName) {
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename={$fileName}");
            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            $template->saveAs("php://output");
        });
    }
}
