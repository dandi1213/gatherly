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
                ->editColumn('role', fn ($model) => roleBadge($model->role))
                ->editColumn('is_active', fn ($model) => $model->is_active
                        ? '<span class="text-green-600">Aktif</span>'
                        : '<span class="text-red-600">Nonaktif</span>'
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
