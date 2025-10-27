<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    public function index()
    {
        $slides = Slide::latest()
            ->paginate(10);

        $publishedCount = Slide::where('publish', true)->count();

        return view('admin.slides.index', compact('slides', 'publishedCount'));
    }

    public function create()
    {
        return view('admin.slides.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:191',
            'sub_title' => 'nullable|string|max:191',
            'btn_text' => 'nullable|string|max:191',
            'btn_url' => 'nullable|string|max:191',
            'publish' => 'sometimes|boolean',
            'description' => 'nullable|string|max:500',
            'image' => 'required|image|max:2048',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        $slide = Slide::create($validated);

        // آپلود تصویر اسلاید
        if ($request->hasFile('image')) {
            $slide->addMedia($request->file('image'))
                ->toMediaCollection('slide_image');
        }

        return redirect()->route('admin.slides.index')
            ->with('success', 'اسلاید با موفقیت ایجاد شد.');
    }

    public function show(Slide $slide)
    {
        return view('admin.slides.show', compact('slide'));
    }

    public function edit(Slide $slide)
    {
        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, Slide $slide)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:191',
            'sub_title' => 'nullable|string|max:191',
            'btn_text' => 'nullable|string|max:191',
            'btn_url' => 'nullable|string|max:191',
            'publish' => 'sometimes|boolean',
            'description' => 'nullable|string|max:500',
            'image' => 'sometimes|image|max:2048',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        $slide->update($validated);

        // آپلود تصویر اسلاید
        if ($request->hasFile('image')) {
            $slide->clearMediaCollection('slide_image');
            $slide->addMedia($request->file('image'))
                ->toMediaCollection('slide_image');
        }

        return redirect()->route('admin.slides.index')
            ->with('success', 'اسلاید با موفقیت ویرایش شد.');
    }

    public function destroy(Slide $slide)
    {
        // حذف رسانه‌ها
        $slide->clearMediaCollection('slide_image');

        $slide->delete();

        return redirect()->route('admin.slides.index')
            ->with('success', 'اسلاید با موفقیت حذف شد.');
    }

    public function togglePublish(Slide $slide)
    {
        $slide->update(['publish' => !$slide->publish]);
        $status = $slide->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "اسلاید {$status} شد.");
    }

    public function reorder(Request $request)
    {
        $orderData = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'exists:slides,id',
            'order.*.order' => 'integer|min:0',
        ]);

        foreach ($orderData['order'] as $item) {
            Slide::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'ترتیب اسلایدها به‌روز شد.']);
    }

    public function published()
    {
        $slides = Slide::where('publish', true)
            ->orderBy('order')
            ->get();

        return view('admin.slides.published', compact('slides'));
    }
}
