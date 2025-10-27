<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('category')
            ->latest()
            ->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $categories = ServiceCategory::where('publish', true)->get();
        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'title' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:services',
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'intro_text' => 'required|string',
            'body' => 'required|string',
            'publish' => 'sometimes|boolean',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // ایجاد alias خودکار
        if (empty($validated['alias'])) {
            $validated['alias'] = Str::slug($validated['title']);
        }

        // مقادیر boolean
        $validated['publish'] = $request->has('publish');
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        $service = Service::create($validated);

        // آپلود تصویر شاخص
        if ($request->hasFile('featured_image')) {
            $service->addMedia($request->file('featured_image'))
                ->toMediaCollection('featured_image');
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'خدمت با موفقیت ایجاد شد.');
    }

    public function show(Service $service)
    {
        $service->load('category');
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::where('publish', true)->get();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'title' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:services,alias,' . $service->id,
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'intro_text' => 'required|string',
            'body' => 'required|string',
            'publish' => 'sometimes|boolean',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // مقادیر boolean
        $validated['publish'] = $request->has('publish');
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        $service->update($validated);

        // آپلود تصویر شاخص
        if ($request->hasFile('featured_image')) {
            $service->clearMediaCollection('featured_image');
            $service->addMedia($request->file('featured_image'))
                ->toMediaCollection('featured_image');
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'خدمت با موفقیت ویرایش شد.');
    }

    public function destroy(Service $service)
    {
        // حذف رسانه‌ها
        $service->clearMediaCollection('featured_image');

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'خدمت با موفقیت حذف شد.');
    }

    public function togglePublish(Service $service)
    {
        $service->update(['publish' => !$service->publish]);
        $status = $service->publish ? 'منتشر' : 'پنهان';
        return redirect()->back()->with('success', "خدمت {$status} شد.");
    }

    public function published()
    {
        $services = Service::with('category')
            ->where('publish', true)
            ->latest()
            ->get();

        return view('admin.services.published', compact('services'));
    }
}
