<?php

namespace App\Http\Controllers;

use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    public function index()
    {
        $faqCategories = FaqCategory::withCount('faqs')
            ->latest()
            ->paginate(10);

        return view('admin.faq-categories.index', compact('faqCategories'));
    }

    public function create()
    {
        return view('admin.faq-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191|unique:faq_categories',
            'publish' => 'sometimes|boolean',
        ]);

        $validated['publish'] = $request->has('publish');

        FaqCategory::create($validated);

        return redirect()->route('admin.faq-categories.index')
            ->with('success', 'دسته‌بندی سوالات متداول با موفقیت ایجاد شد.');
    }

    public function show(FaqCategory $faqCategory)
    {
        $faqs = $faqCategory->faqs()->where('publish', true)->get();
        return view('admin.faq-categories.show', compact('faqCategory', 'faqs'));
    }

    public function edit(FaqCategory $faqCategory)
    {
        return view('admin.faq-categories.edit', compact('faqCategory'));
    }

    public function update(Request $request, FaqCategory $faqCategory)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191|unique:faq_categories,title,' . $faqCategory->id,
            'publish' => 'sometimes|boolean',
        ]);

        $validated['publish'] = $request->has('publish');

        $faqCategory->update($validated);

        return redirect()->route('admin.faq-categories.index')
            ->with('success', 'دسته‌بندی سوالات متداول با موفقیت ویرایش شد.');
    }

    public function destroy(FaqCategory $faqCategory)
    {
        // بررسی وجود سوالات
        if ($faqCategory->faqs()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف دسته‌بندی دارای سوال وجود ندارد. ابتدا سوالات مربوطه را حذف کنید.');
        }

        $faqCategory->delete();

        return redirect()->route('admin.faq-categories.index')
            ->with('success', 'دسته‌بندی سوالات متداول با موفقیت حذف شد.');
    }

    public function togglePublish(FaqCategory $faqCategory)
    {
        $faqCategory->update(['publish' => !$faqCategory->publish]);
        $status = $faqCategory->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "دسته‌بندی {$status} شد.");
    }
}
