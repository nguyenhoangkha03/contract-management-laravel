<?php

namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromCollection;

class ContractExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $contract;

    public function __construct($contract)
    {
        $this->contract = $contract;
    }

    public function collection()
    {
        return collect([
            [
                'Start Date' => $this->contract->start_date,
                'End Date' => $this->contract->end_date,
                'Total Value' => $this->contract->total_value,
                'Status' => $this->contract->status,
                'Description' => $this->contract->description,
            ]
        ]);
    }

    public function headings(): array
    {
        return ["Start Date", "End Date", "Total Value", "Status", "Description"];
    }
}
