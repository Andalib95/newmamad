<?php

namespace App\Http\Controllers;

use App\Models\MobileMenuItem;
use Illuminate\Http\Request;

class MobileMenuItemController extends Controller
{
    public function index()
    {
        $mobileMenuItems = MobileMenuItem::with(['parent', 'children'])
            ->orderBy('order')
            ->get();

        $parentItems = $mobileMenuItems->where('parent_id', -1);

        return view('admin.mobile-menu-items.index', compact('mobileMenuItems', 'parentItems'));
    }

    public function create()
    {
        $parents = MobileMenuItem::where('publish', true)
            ->where('parent_id', -1)
            ->get();

        return view('admin.mobile-menu-items.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'titre' => 'required|string|max:191',
            'url' => 'required|string|max:191',
            'publish' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
        ]);

        $validated['publish'] = $request->has('publish');

        MobileMenuItem::create($validated);

        return redirect()->route('admin.mobile-menu-items.index')
            ->with('success', 'آیتم منوی موبایل با موفقیت ایجاد شد.');
    }

    public function show(MobileMenuItem $mobileMenuItem)
    {
        $mobileMenuItem->load(['parent', 'children']);
        return view('admin.mobile-menu-items.show', compact('mobileMenuItem'));
    }

    public function edit(MobileMenuItem $mobileMenuItem)
    {
        $parents = MobileMenuItem::where('publish', true)
            ->where('id', '!=', $mobileMenuItem->id)
            ->where('parent_id', -1)
            ->get();

        return view('admin.mobile-menu-items.edit', compact('mobileMenuItem', 'parents'));
    }

    public function update(Request $request, MobileMenuItem $mobileMenuItem)
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'titre' => 'required|string|max:191',
            'url' => 'required|string|max:191',
            'publish' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
        ]);

        $validated['publish'] = $request->has('publish');

        $mobileMenuItem->update($validated);

        return redirect()->route('admin.mobile-menu-items.index')
            ->with('success', 'آیتم منوی موبایل با موفقیت ویرایش شد.');
    }

    public function destroy(MobileMenuItem $mobileMenuItem)
    {
        if ($mobileMenuItem->children()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف آیتم منوی دارای زیرمنو وجود ندارد.');
        }

        $mobileMenuItem->delete();

        return redirect()->route('admin.mobile-menu-items.index')
            ->with('success', 'آیتم منوی موبایل با موفقیت حذف شد.');
    }

    public function togglePublish(MobileMenuItem $mobileMenuItem)
    {
        $mobileMenuItem->update(['publish' => !$mobileMenuItem->publish]);
        $status = $mobileMenuItem->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "آیتم منوی موبایل {$status} شد.");
    }

    public function reorder(Request $request)
    {
        $orderData = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'exists:mobile_menu_items,id',
            'order.*.order' => 'integer|min:0',
        ]);

        foreach ($orderData['order'] as $item) {
            MobileMenuItem::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'ترتیب منوی موبایل به‌روز شد.']);
    }
}
