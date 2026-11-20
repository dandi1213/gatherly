<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(User::query())
                ->filterColumn('name', function ($model) use ($request) {
                    $model->where('name', 'like', '%'.$request->get('search')['value'].'%');
                })
                ->filterColumn('email', function ($model) use ($request) {
                    $model->where('email', 'like', '%'.$request->get('search')['value'].'%');
                })
                ->editColumn('role', fn ($model) => '<span class="inline-flex rounded-full border border-line bg-canvas px-2 py-0.5 text-xs font-semibold text-muted">'.e(ucfirst($model->role)).'</span>')
                ->editColumn('is_active', fn ($model) => $model->is_active
                        ? '<span class="inline-flex items-center gap-1.5 rounded-full border border-success-100 bg-success-50 px-2 py-0.5 text-xs font-semibold text-success-700"><span class="h-1.5 w-1.5 rounded-full bg-success-700"></span>Aktif</span>'
                        : '<span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-2 py-0.5 text-xs font-semibold text-muted"><span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>Nonaktif</span>'
                )
                ->editColumn('actions', fn ($model) => view('admin.users.partials._actions', ['user' => $model]))
                ->rawColumns(['role', 'is_active', 'actions'])
                ->make(true);
        }

        $users = User::latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function toggle(Request $request, User $user)
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', 'User '.($user->is_active ? 'diakitfkan' : 'dinonaktifkan').' berhasil.');
    }
}
