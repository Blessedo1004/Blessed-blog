<?php

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Validate;

new class extends Component
{
    #[Validate('required|string|min:3|max:10|unique:categories,name')]
    public string $name;

    #[Validate('required|string|min:3|max:255')]
    public string $description;

     #[Validate('required|string')]
    public string $color;

    public function create(){
        $this->validate();

        $category = new Category();
        $category->name = $this->name;
        $category->description = $this->description;
        $category->color = $this->color;

        $category->save();

        session()->flash('success', 'Category Created Successfully');
        $this->redirect(route('categories.index'), navigate:true);
    }
};
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Category</h1>
    </div>

    {{-- form --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form wire:submit="create" class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Name
                </label>
                <input 
                    type="text"
                    id="name"
                    wire:model.live.debounce="name" 
                    placeholder="Enter category name"
                    autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <input 
                    type="text"
                    id="description"
                    wire:model.live.debounce="description" 
                    placeholder="Enter category description"
                    autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">
                    Color
                </label>
                <input 
                    type="text"
                    id="color"
                    wire:model.live.debounce="color" 
                    placeholder="Enter category color"
                    autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Create Category
                </button>
                <a 
                    href="{{ route('categories.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>