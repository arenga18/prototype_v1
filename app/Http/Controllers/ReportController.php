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
            'report_type' => 'required|string|in:full-recap,KS,nonKS,K+Eris,flatpack,Kaca,frame-alu-yn,frame-alu-ad,BPB-setting,BPB-setting-frame,BPB-raw-material,handle,handle+,cutting-ks,cutting-nonks',
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
            'Kaca' => 'livewire.reports.kaca',
            'frame-alu-yn' => 'livewire.reports.frame-alu-yn',
            'frame-alu-ad' => 'livewire.reports.frame-alu-ad',
            'BPB-setting' => 'livewire.reports.BPB-setting',
            'BPB-setting-frame' => 'livewire.reports.BPB-setting-frame',
            'BPB-raw-material' => 'livewire.reports.BPB-raw-material',
            'handle' => 'livewire.reports.handle',
            'handle+' => 'livewire.reports.handle+',
            'cutting-ks' => 'livewire.reports.cutting-ks',
            'cutting-nonks' => 'livewire.reports.cutting-nonks',
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
