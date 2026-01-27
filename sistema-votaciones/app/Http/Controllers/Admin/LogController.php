<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('admin')->orderBy('created_at', 'desc');
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        $logs = $query->paginate(50);
        return view('admin.logs', compact('logs'));
    }
}
