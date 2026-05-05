<?php

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Validate;

new class extends Component
{
    public Category $category;

    #[Validate('required|string|min:3|max:10|unique:categories,name')]
    public string $name;

    #[Validate('required|string|min:3|max:255')]
    public string $description;

     #[Validate('required|string')]
    public string $color;

    public function mount(Category $category){
        // Authorization check
        if (!auth()->user()->can('edit all posts')) {
            abort(403);
        }

        $this->category = $category;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->color = $category->color;
    }

    public function update(){
        $this->validate();

        $this->category->name = $this->name;
        $this->category->description = $this->description;
        $this->category->color = $this->color;
        $this->category->save();

        session()->flash('success', 'Category Updated');
        $this->redirect(route('categories.index'), navigate:true);

    }

};
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
        <p class="mt-1 text-sm text-gray-600">Update your blog post</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form wire:submit="update" class="space-y-6">
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

                        <!-- Description-->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    description
                </label>
                <textarea 
                    id="description"
                    wire:model="description" 
                    placeholder="A short summary of your post (optional)"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!--  Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">
                    Color
                </label>
                <input 
                    id="color"
                    type="text"
                    wire:model.live.debounce="color" 
                    placeholder="Category Color"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button 
                type="update" 
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                Update Category
            </button>
            <a 
                href="{{ route('categories.index') }}" 
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                Cancel
            </a>
        </div>
    </form>
</div>
</div>