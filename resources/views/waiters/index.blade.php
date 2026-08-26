@extends('layouts.app')
@section('title', 'Waiters')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Waiters</h1>
        <p class="text-slate-400 mt-1">Manage your catering staff</p>
    </div>
    <a href="{{ route('waiters.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start">
        <i class="fas fa-plus"></i> Add Waiter
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($waiters as $waiter)
                <tr class="table-row group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr($waiter->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800 group-hover:text-orange-600 transition">{{ $waiter->user->name }}</div>
                                <div class="text-sm text-slate-400">{{ $waiter->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $waiter->phone }}</td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs {{ $waiter->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($waiter->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $waiter->date_hired?->format('M d, Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('waiters.show', $waiter) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('waiters.edit', $waiter) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form action="{{ route('waiters.destroy', $waiter) }}" method="POST" class="inline" onsubmit="return confirm('Delete this waiter?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition" title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($waiters->isEmpty())
    <div class="px-6 py-12 text-center text-slate-400">
        <i class="fas fa-users text-4xl mb-3 text-slate-200"></i>
        <p>No waiters found.</p>
    </div>
    @endif
</div>

<div class="mt-6">
    {{ $waiters->links('pagination::tailwind') }}
</div>
@endsection