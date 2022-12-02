<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductExport implements FromQuery, WithMapping, WithHeadings, WithColumnWidths, WithStyles, WithColumnFormatting
{

    protected $startDate, $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return Product::query()->whereDate('publish_date', '>=', $this->startDate)->whereDate('publish_date', '<=', $this->endDate);
    }

    public function headings(): array
    {
        return ['EBAY ID', 'EBAY URL', 'DESCRIPTION', 'PUBLISHER', 'PUBLISH DATE'];
    }

    /**
     * @var Product $invoice
     */
    public function map($product): array
    {
        return [
            $product->ebay_id,
            $product->ebay_url,
            $product->description,
            $product->publisher,
            $product->publish_date,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 100,
            'C' => 80,
            'D' => 30,
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font'      => ['bold' => true]],
            'A'  => ['alignment' => ['wrapText' => true]],
            'B'  => ['alignment' => ['wrapText' => true]],
            'C'  => ['alignment' => ['wrapText' => true]],
            'D'  => ['alignment' => ['wrapText' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => '@',
            'B' => '@',
            'C' => '@',
            'D' => '@',
        ];
    }
}
