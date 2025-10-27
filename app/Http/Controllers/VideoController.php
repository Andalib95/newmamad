<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()
            ->paginate(12);

        $publishedCount = Video::where('publish', true)->count();

        return view('admin.videos.index', compact('videos', 'publishedCount'));
    }

    public function create()
    {
        return view('admin.videos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:500',
            'video_url' => 'required|string|max:500',
            'thumbnail' => 'nullable|image|max:2048',
            'publish' => 'sometimes|boolean',
            'duration' => 'nullable|string|max:20',
            'views' => 'nullable|integer|min:0',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');
        $validated['views'] = $validated['views'] ?? 0;

        $video = Video::create($validated);

        // آپلود تامبنیل
        if ($request->hasFile('thumbnail')) {
            $video->addMedia($request->file('thumbnail'))
                ->toMediaCollection('thumbnail');
        }

        return redirect()->route('admin.videos.index')
            ->with('success', 'ویدئو با موفقیت ایجاد شد.');
    }

    public function show(Video $video)
    {
        // افزایش تعداد بازدید
        $video->increment('views');

        return view('admin.videos.show', compact('video'));
    }

    public function edit(Video $video)
    {
        return view('admin.videos.edit', compact('video'));
    }

    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:500',
            'video_url' => 'required|string|max:500',
            'thumbnail' => 'sometimes|image|max:2048',
            'publish' => 'sometimes|boolean',
            'duration' => 'nullable|string|max:20',
            'views' => 'nullable|integer|min:0',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        $video->update($validated);

        // آپلود تامبنیل
        if ($request->hasFile('thumbnail')) {
            $video->clearMediaCollection('thumbnail');
            $video->addMedia($request->file('thumbnail'))
                ->toMediaCollection('thumbnail');
        }

        return redirect()->route('admin.videos.index')
            ->with('success', 'ویدئو با موفقیت ویرایش شد.');
    }

    public function destroy(Video $video)
    {
        // حذف رسانه‌ها
        $video->clearMediaCollection('thumbnail');

        $video->delete();

        return redirect()->route('admin.videos.index')
            ->with('success', 'ویدئو با موفقیت حذف شد.');
    }

    public function togglePublish(Video $video)
    {
        $video->update(['publish' => !$video->publish]);
        $status = $video->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "ویدئو {$status} شد.");
    }

    public function incrementViews(Video $video)
    {
        $video->increment('views');
        return response()->json(['views' => $video->views]);
    }

    public function reorder(Request $request)
    {
        $orderData = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'exists:videos,id',
            'order.*.order' => 'integer|min:0',
        ]);

        foreach ($orderData['order'] as $item) {
            Video::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'ترتیب ویدئوها به‌روز شد.']);
    }

    public function published()
    {
        $videos = Video::where('publish', true)
            ->orderBy('order')
            ->get();

        return view('admin.videos.published', compact('videos'));
    }
}
