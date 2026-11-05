@extends('layouts.app')

@section('title', 'Edit Setting')

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

        <form method="POST" action="{{ route('settings.update', $setting) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
                <input type="text" value="{{ $setting->key }}" disabled
                       class="w-full border border-gray-200 bg-gray-50 rounded px-3 py-2 text-sm text-gray-500">
                <p class="text-xs text-gray-400 mt-1">Setting keys can't be changed — only the value.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                <input type="text" name="value" value="{{ old('value', $setting->value) }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
                    Save Changes
                </button>
                <a href="{{ route('settings.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
