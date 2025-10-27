<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['parent', 'children', 'articles'])
            ->orderBy('order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::where('publish', true)->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'title' => 'required|string|max:191',
            'titre' => 'nullable|string|max:191',
            'alias' => 'required|string|max:191|unique:categories',
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'body' => 'nullable|string',
            'publish' => 'sometimes|boolean',
            'blog' => 'sometimes|boolean',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        // ایجاد alias خودکار
        if (empty($validated['alias'])) {
            $validated['alias'] = Str::slug($validated['title']);
        }

        // مقادیر boolean
        $validated['publish'] = $request->has('publish');
        $validated['blog'] = $request->has('blog');
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        $category = Category::create($validated);

        // آپلود تصویر
        if ($request->hasFile('image')) {
            $category->addMedia($request->file('image'))
                ->toMediaCollection('category_image');
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'articles']);
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parents = Category::where('publish', true)
            ->where('id', '!=', $category->id)
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'title' => 'required|string|max:191',
            'titre' => 'nullable|string|max:191',
            'alias' => 'required|string|max:191|unique:categories,alias,' . $category->id,
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'body' => 'nullable|string',
            'publish' => 'sometimes|boolean',
            'blog' => 'sometimes|boolean',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        // مقادیر boolean
        $validated['publish'] = $request->has('publish');
        $validated['blog'] = $request->has('blog');
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        $category->update($validated);

        // آپلود تصویر
        if ($request->hasFile('image')) {
            $category->clearMediaCollection('category_image');
            $category->addMedia($request->file('image'))
                ->toMediaCollection('category_image');
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ویرایش شد.');
    }

    public function destroy(Category $category)
    {
        // بررسی وجود زیردسته‌ها
        if ($category->children()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف دسته‌بندی دارای زیردسته وجود ندارد.');
        }

        // بررسی وجود مقالات
        if ($category->articles()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف دسته‌بندی دارای مقاله وجود ندارد.');
        }

        // حذف رسانه‌ها
        $category->clearMediaCollection('category_image');

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }

    // متدهای اضافی برای مدیریت وضعیت
    public function togglePublish(Category $category)
    {
        $category->update(['publish' => !$category->publish]);
        $status = $category->publish ? 'منتشر' : 'غیرفعال';
        return redirect()->back()->with('success', "دسته‌بندی {$status} شد.");
    }
}
