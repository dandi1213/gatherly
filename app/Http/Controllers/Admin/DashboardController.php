<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalOrganizers = User::where('role', 'organizer')->count();
        $totalEvents = Event::count();
        $totalPublished = Event::where('status', 'published')->count();
        $totalDraft = Event::where('status', 'draft')->count();
        $totalCancelled = Event::where('status', 'cancelled')->count();
        $totalRegistrations = User::first()?->registrations()->count() ?? 0;

        // Event terbaru
        $recentEvents = Event::latest()->take(5)->get(['id', 'title', 'status', 'created_at']);

        // Daftar user terbaru
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'role', 'created_at']);

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalOrganizers',
            'totalEvents',
            'totalPublished',
            'totalDraft',
            'totalCancelled',
            'totalRegistrations',
            'recentEvents',
            'recentUsers'
        ));
    }
}
