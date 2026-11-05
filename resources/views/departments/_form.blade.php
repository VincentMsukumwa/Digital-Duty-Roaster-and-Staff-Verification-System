@csrf

<div class="space-y-5">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
        <input type="text" name="name" value="{{ old('name', $department?->name) }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(optional)</span></label>
        <textarea name="description" rows="3"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $department?->description) }}</textarea>
    </div>

</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
        {{ $department ? 'Save Changes' : 'Add Department' }}
    </button>
    <a href="{{ route('departments.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
</div>
