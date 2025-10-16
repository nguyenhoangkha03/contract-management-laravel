<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractExportController extends Controller
{
    public function exportExcel()
    {
        $contracts = Contract::with('client', 'contractType')->get();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(public_path('templates/contract_template.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

        $row = 3;
        foreach ($contracts as $contract) {
            $sheet->fromArray([
                $contract->contractType->name
            ], null, "A1");

            $sheet->fromArray([
                $contract->contract_number,
                $contract->client->name,
                $contract->contractType->name,
                $contract->start_date,
                $contract->end_date,
                $contract->total_value,
                $contract->status,
                $contract->description,
                $contract->created_at,
                $contract->updated_at
            ], null, "A$row");
            $row++;
        }

        $fileName = 'contracts.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$fileName\""
        ]);
    }
}
