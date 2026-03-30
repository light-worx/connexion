<?php

namespace App\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use Lightworx\FilamentReports\Reports\Concerns\HasSections;
use Lightworx\FilamentReports\Reports\Concerns\HasTables;
use App\Models\Group;

class GroupReport extends BaseReport
{
    use HasSections, HasTables;

    protected $group;

    public function __construct()
    {
        parent::__construct();
        $this->config['footer']['enabled'] = false;
    }

    public static function routes(): void
    {
        Route::get('/admin/people/reports/groups/{id}', function ($groupId) {
            $group = Group::with('groupmembers')->findOrFail($groupId);
            return (new static())->setGroup($group)->handle();
        })->name('reports.group');
    }

    public function setGroup($group): static
    {
        $this->group = $group;
        return $this;
    }

    public function generate(): void
    {
        $this->setReportTitle($this->group->groupname);
        $this->AddPage();

        // Group details section
        $this->renderSectionTitle('Group Information');
        $this->renderKeyValueBlock([
            'Group Name' => $this->group->groupname,
            'Leader' => $this->group->individual->fullname ?? 'N/A',
            'Meeting Day' => $this->group->meetingday ?? 'N/A',
            'Meeting Time' => $this->group->meetingtime ?? 'N/A',
            'Total Members' => $this->group->groupmembers->count(),
        ]);

        $this->renderDivider();
        
        // Members list
        $this->renderSectionTitle('Members');
        
        if ($this->group->groupmembers->isEmpty()) {
            $this->renderText('No members in this group.');
        } else {
            // Prepare member data
            $memberRows = $this->group->groupmembers->map(function ($member) {
                return [
                    $member->individual->fullname,
                    $member->individual->cellphone ?? 'N/A',
                    date('d M',strtotime($member->individual->birthdate)) ?? 'N/A',
                    $member->individual->email ?? '',
                ];
            })->toArray();

            $this->renderTable(
                headers: ['Name', 'Phone', 'Birthday', 'Email'],
                rows: $memberRows,
                columnWidths: [50, 35, 30, 65]
            );
        }
    }

    protected function getFilename(): string
    {
        return 'group-report-' . now()->format('Y-m-d') . '.pdf';
    }
}