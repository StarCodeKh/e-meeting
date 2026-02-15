<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduleType; // កុំភ្លេច Import Model
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ScheduleTypeController extends Controller
{
    /**
     * ទាញយកជម្រើសទាំងអស់សម្រាប់ប្រើក្នុង Form
     */
    public function index() 
    {
        try {
            $types = ScheduleType::where('is_active', true)->orderBy('sort_order', 'asc')->get();
            $priorities = DB::table('priorities')->orderBy('sort_order', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'types'      => $types,
                    'priorities' => $priorities
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}