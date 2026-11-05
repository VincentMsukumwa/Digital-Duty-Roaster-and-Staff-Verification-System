@extends('layouts.app')

@section('title', 'Add User Account')

@section('content')
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 p-8 max-w-2xl">
        @php($user = null)

        @if ($errors->any())
            <div class="bg-red-50 border border-red-100 text-red-700 text-sm px-4 py-3 rounded-xl mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @include('users._form')
        </form>
    </div>
@endsection
