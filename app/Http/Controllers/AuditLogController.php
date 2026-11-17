<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    private function checkAccess(): void
    {
        $user = Auth::user();
        if (!$user?->isSuperAdmin()) {
            abort(403, 'Akses terbatas untuk Super Admin.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAccess();

        $query = LogAudit::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('entitas')) {
            $query->where('entitas', $request->entitas);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('aksi', $like, "%{$q}%")
                    ->orWhere('entitas', $like, "%{$q}%");
            });
        }

        $logs = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $entities = LogAudit::distinct()->pluck('entitas');

        return view('audit.index', compact('logs', 'users', 'entities'));
    }
}
