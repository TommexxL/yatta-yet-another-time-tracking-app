<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryStatus;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeEntryController extends Controller
{
    public function clockIn(): RedirectResponse
    {
        $user = Auth::user();

        $existingEntry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('clock_out')
            ->first();

        if ($existingEntry) {
            return back()->with('error', 'You are already clocked in.');
        }

        TimeEntry::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->format('H:i:s'),
            'status' => TimeEntryStatus::Approved,
        ]);

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(): RedirectResponse
    {
        $user = Auth::user();

        $entry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if (! $entry) {
            return back()->with('error', 'No active clock-in found.');
        }

        $entry->update([
            'clock_out' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Clocked out successfully.');
    }
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
