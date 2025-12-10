<?php

namespace App\Http\Controllers;

use App\Exports\Dgii606Export;
use App\Exports\Dgii607Export;
use App\Exports\Dgii608Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DgiiReportController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('dgii report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $months = [
            1 => __('January'),
            2 => __('February'),
            3 => __('March'),
            4 => __('April'),
            5 => __('May'),
            6 => __('June'),
            7 => __('July'),
            8 => __('August'),
            9 => __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December'),
        ];

        $year = (int) date('Y');
        $years = collect(range($year - 4, $year + 1))->mapWithKeys(fn ($y) => [$y => $y]);

        return view('report.dgii', compact('months', 'years'));
    }

    public function export(Request $request)
    {
        if (! Auth::user()->can('dgii report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:3000',
            'format' => 'required|in:606,607,608',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;
        $creatorId = Auth::user()->creatorId();

        return match ($request->format) {
            '606' => Excel::download(new Dgii606Export($month, $year, $creatorId), "DGII-606-{$year}-{$month}.xlsx"),
            '607' => Excel::download(new Dgii607Export($month, $year, $creatorId), "DGII-607-{$year}-{$month}.xlsx"),
            default => Excel::download(new Dgii608Export($month, $year, $creatorId), "DGII-608-{$year}-{$month}.xlsx"),
        };
    }
}
