<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function collection()
    {
        return $this->project->tasks()->with('assignedTo')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Title', 'Status', 'Priority', 'Assigned To', 'Due Date', 'Created At'];
    }

    public function map($task): array
    {
        return [
            $task->id,
            $task->title,
            ucfirst($task->status),
            ucfirst($task->priority),
            $task->assignedTo?->name ?? 'Unassigned',
            $task->due_date ? date('Y-m-d', strtotime($task->due_date)) : 'N/A',
            $task->created_at?->format('Y-m-d'),
        ];
    }

    public function title(): string
    {
        return 'Tasks Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // تنسيق الصف الأول (Headings)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '1E293B'], // لون كحلي داكن راقي
                ],
            ],
        ];
    }
}