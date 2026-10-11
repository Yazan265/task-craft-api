<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Exports\ProjectReportExport;
use App\Models\Project;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reports', description: 'Endpoints for generating project reports and exports')]
class ReportController extends Controller
{
    use AuthorizesRequests;

    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    #[OA\Get(
        path: '/api/projects/{project}/reports/summary',
        summary: 'Get project summary report',
        tags: ['Reports'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project summary report generated successfully'),
            new OA\Response(response: 403, description: 'Unauthorized access to project reports'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function summary(Project $project): JsonResponse
    {
        $this->authorize('viewReport', $project);

        $summary = $this->reportService->getProjectSummary($project);

        return response()->json([
            'message' => 'Project summary report generated successfully!',
            'data' => $summary
        ], 200);
    }

    #[OA\Get(
        path: '/api/projects/{project}/reports/export/excel',
        summary: 'Export project report as Excel file',
        tags: ['Reports'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Excel file generated successfully',
                content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ),
            new OA\Response(response: 403, description: 'Unauthorized access'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function exportExcel(Project $project)
    {
        $this->authorize('export', $project);

        return Excel::download(
            new ProjectReportExport($project),
            "project-{$project->id}-report.xlsx"
        );
    }

    #[OA\Get(
        path: '/api/projects/{project}/reports/export/pdf',
        summary: 'Export project report as PDF file',
        tags: ['Reports'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF file generated successfully',
                content: new OA\MediaType(mediaType: 'application/pdf')
            ),
            new OA\Response(response: 403, description: 'Unauthorized access'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function exportPdf(Project $project)
    {
        $this->authorize('export', $project);

        $summary = $this->reportService->getProjectSummary($project);
        $project->load('tasks');
        $pdf = Pdf::loadView('reports.project-pdf', compact('project', 'summary'));

        return $pdf->download("project-{$project->id}-report.pdf");
    }
}