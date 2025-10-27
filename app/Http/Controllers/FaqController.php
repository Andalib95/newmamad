<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::with('category')
            ->latest()
            ->paginate(15);

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        $categories = FaqCategory::where('publish', true)->get();
        return view('admin.faqs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'show_in_home' => 'sometimes|boolean',
            'publish' => 'sometimes|boolean',
        ]);

        $validated['show_in_home'] = $request->has('show_in_home');
        $validated['publish'] = $request->has('publish');

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'سوال متداول با موفقیت ایجاد شد.');
    }

    public function show(Faq $faq)
    {
        $faq->load('category');
        return view('admin.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq)
    {
        $categories = FaqCategory::where('publish', true)->get();
        return view('admin.faqs.edit', compact('faq', 'categories'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'show_in_home' => 'sometimes|boolean',
            'publish' => 'sometimes|boolean',
        ]);

        $validated['show_in_home'] = $request->has('show_in_home');
        $validated['publish'] = $request->has('publish');

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'سوال متداول با موفقیت ویرایش شد.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', 'سوال متداول با موفقیت حذف شد.');
    }

    public function togglePublish(Faq $faq)
    {
        $faq->update(['publish' => !$faq->publish]);
        $status = $faq->publish ? 'منتشر' : 'پنهان';
        return redirect()->back()->with('success', "سوال {$status} شد.");
    }

    public function toggleHome(Faq $faq)
    {
        $faq->update(['show_in_home' => !$faq->show_in_home]);
        $status = $faq->show_in_home ? 'به' : 'از';
        return redirect()->back()->with('success', "سوال {$status} صفحه اصلی حذف شد.");
    }
}
