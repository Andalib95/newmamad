<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $serviceCategories = ServiceCategory::withCount('services')
            ->latest()
            ->paginate(10);

        return view('admin.service-categories.index', compact('serviceCategories'));
    }

    public function create()
    {
        return view('admin.service-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191|unique:service_categories',
            'description' => 'nullable|string|max:500',
            'publish' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        ServiceCategory::create($validated);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'دسته‌بندی خدمات با موفقیت ایجاد شد.');
    }

    public function show(ServiceCategory $serviceCategory)
    {
        $services = $serviceCategory->services()
            ->where('publish', true)
            ->latest()
            ->get();

        return view('admin.service-categories.show', compact('serviceCategory', 'services'));
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        return view('admin.service-categories.edit', compact('serviceCategory'));
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191|unique:service_categories,title,' . $serviceCategory->id,
            'description' => 'nullable|string|max:500',
            'publish' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        $serviceCategory->update($validated);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'دسته‌بندی خدمات با موفقیت ویرایش شد.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        // بررسی وجود خدمات
        if ($serviceCategory->services()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف دسته‌بندی دارای خدمات وجود ندارد. ابتدا خدمات مربوطه را حذف کنید.');
        }

        $serviceCategory->delete();

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'دسته‌بندی خدمات با موفقیت حذف شد.');
    }

    public function togglePublish(ServiceCategory $serviceCategory)
    {
        $serviceCategory->update(['publish' => !$serviceCategory->publish]);
        $status = $serviceCategory->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "دسته‌بندی {$status} شد.");
    }

    public function reorder(Request $request)
    {
        $orderData = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:service_categories,id',
        ]);

        foreach ($orderData['order'] as $index => $id) {
            ServiceCategory::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'ترتیب با موفقیت به‌روز شد.']);
    }
}
