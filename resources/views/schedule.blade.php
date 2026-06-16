@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h1>This Week</h1>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry->date->format('D d/m') }}</td>
                    <td>{{ $entry->clock_in }}</td>
                    <td>{{ $entry->clock_out ?? '-' }}</td>

                    <td>
                        <a href="{{ route('time-entry.correction.create', $entry) }}">
                            Request Change
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection