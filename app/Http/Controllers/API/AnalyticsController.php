<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function getSummary(Request $request)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth());
        $end = $request->query('end_date', Carbon::now()->endOfMonth());

        $data = [
            'total_meetings'    => Schedule::whereBetween('date', [$start, $end])->count(),
            'total_users'       => User::count(),
            'total_attachments' => Schedule::whereBetween('date', [$start, $end])->whereNotNull('attachment')->count(),
            'upcoming_meetings' => Schedule::where('date', '>', now())->count(),
        ];

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function getChartData(Request $request)
    {
        $start = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()));
        $end = Carbon::parse($request->query('end_date', Carbon::now()->endOfMonth()));

        $activities = Schedule::whereBetween('date', [$start, $end])
            ->select(DB::raw('DATE(date) as day'), DB::raw('count(*) as count'))
            ->groupBy('day')
            ->get();

        $labels = [];
        $values = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateString = $date->toDateString();
            $labels[] = $date->format('d M');
            $found = $activities->firstWhere('day', $dateString);
            $values[] = $found ? $found->count : 0;
        }

        return response()->json(['status' => 'success', 'labels' => $labels, 'values' => $values]);
    }
}