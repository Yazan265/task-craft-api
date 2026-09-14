<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectReportTest extends TestCase
{
    use RefreshDatabase;

    // --- اختبارات المالك (Owner) ---

    #[Test]
    public function owner_can_get_project_summary_report()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        Task::factory()->create(['project_id' => $project->id, 'status' => 'completed', 'priority' => 'high']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'pending', 'priority' => 'medium']);

        $response = $this->actingAs($owner, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/summary");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'project_id',
                         'project_name',
                         'metrics' => [
                             'total_tasks',
                             'completed_tasks',
                             'pending_tasks',
                             'completion_percentage',
                         ],
                         'priority_breakdown',
                         'status_breakdown',
                     ]
                 ])
                 ->assertJsonPath('data.metrics.total_tasks', 2)
                 ->assertJsonPath('data.metrics.completed_tasks', 1)
                 ->assertJsonPath('data.metrics.pending_tasks', 1)
                 ->assertJsonPath('data.metrics.completion_percentage', 50);
    }

    #[Test]
    public function owner_can_download_excel_report()
    {
        Excel::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/export/excel");

        $response->assertStatus(200);
        
        Excel::assertDownloaded("project-{$project->id}-report.xlsx");
    }

    #[Test]
    public function owner_can_download_pdf_report()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/export/pdf");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    // --- اختبارات الغريب (Stranger) ---

    #[Test]
    public function stranger_cannot_access_project_summary_report()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/summary");

        $response->assertStatus(403);
    }

    #[Test]
    public function stranger_cannot_download_excel_report()
    {
        Excel::fake();

        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/export/excel");

        $response->assertStatus(403);
    }

    #[Test]
    public function stranger_cannot_download_pdf_report()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/export/pdf");

        $response->assertStatus(403);
    }

    // --- اختبارات عضو الفريق (Member) ---

    #[Test]
    public function member_cannot_access_project_summary_report()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        if (method_exists($project, 'members')) {
            $project->members()->attach($member->id);
        }

        $response = $this->actingAs($member, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/summary");

        $response->assertStatus(403);
    }

    #[Test]
    public function member_cannot_download_excel_report()
    {
        Excel::fake();

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        if (method_exists($project, 'members')) {
            $project->members()->attach($member->id);
        }

        $response = $this->actingAs($member, 'api')
                         ->getJson("/api/projects/{$project->id}/reports/export/excel");

        $response->assertStatus(403);
    }
}