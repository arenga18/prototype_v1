<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ReportController extends Controller
{
    public function storeReportData(Request $request, $project)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'report_type' => 'required|string|in:full-recap,KS,nonKS,K+Eris,flatpack',
            'projectInformation' => 'required'
        ]);

        // Store data in session
        session(['breakdown_data' => $validated['data']]);
        session(['spek_data' => $validated['projectInformation']]);

        return response()->json([
            'success' => true,
            'message' => 'Data stored successfully',
            'redirect_url' => route('reports.show', [
                'project' => $project,
                'reportType' => $validated['report_type']
            ])
        ]);
    }

    public function showReport(Request $request, $project, $reportType = null)
    {
        // Get the project
        $project = Project::findOrFail($project);

        // Get data from session
        $reportData = session()->get('breakdown_data', []);
        $spekData = session()->get('spek_data', []);

        // View mapping
        $viewMap = [
            'full-recap' => 'livewire.reports.full-recap',
            'KS' => 'livewire.reports.KS',
            'nonKS' => 'livewire.reports.nonKS',
            'K+Eris' => 'livewire.reports.K+Eris',
            'flatpack' => 'livewire.reports.flatpack',
        ];

        $view = $viewMap[$reportType] ?? 'livewire.reports.full-recap';

        return view($view, [
            'modulBreakdown' => $reportData,
            'spekData' => $spekData,
            'reportType' => $reportType,
            'project' => $project
        ]);
    }
}
