@extends('layouts.app')

@section('title', 'Request Leave')

@section('content')
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 p-8 max-w-lg">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-100 text-red-700 text-sm px-4 py-3 rounded-xl mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('leave-requests.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                <select name="leave_type" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">— Select —</option>
                    @foreach (['Annual', 'Sick', 'Maternity', 'Paternity', 'Compassionate', 'Unpaid'] as $type)
                        <option value="{{ $type }}" @selected(old('leave_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-gray-400">(optional)</span></label>
                <textarea name="reason" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('reason') }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
                    Submit Request
                </button>
                <a href="{{ route('leave-requests.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
