<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::with(['parent', 'children'])
            ->orderBy('order')
            ->get();

        // گروه‌بندی بر اساس والد
        $parentItems = $menuItems->where('parent_id', -1);

        return view('admin.menu-items.index', compact('menuItems', 'parentItems'));
    }

    public function create()
    {
        $parents = MenuItem::where('publish', true)
            ->where('parent_id', -1)
            ->get();

        return view('admin.menu-items.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'titre' => 'required|string|max:191',
            'url' => 'required|string|max:191',
            'type' => 'required|in:normal,dropdown,mega-menu',
            'publish' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
        ]);

        $validated['publish'] = $request->has('publish');

        MenuItem::create($validated);

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'آیتم منو با موفقیت ایجاد شد.');
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load(['parent', 'children']);
        return view('admin.menu-items.show', compact('menuItem'));
    }

    public function edit(MenuItem $menuItem)
    {
        $parents = MenuItem::where('publish', true)
            ->where('id', '!=', $menuItem->id)
            ->where('parent_id', -1)
            ->get();

        return view('admin.menu-items.edit', compact('menuItem', 'parents'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'titre' => 'required|string|max:191',
            'url' => 'required|string|max:191',
            'type' => 'required|in:normal,dropdown,mega-menu',
            'publish' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:191',
        ]);

        $validated['publish'] = $request->has('publish');

        $menuItem->update($validated);

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'آیتم منو با موفقیت ویرایش شد.');
    }

    public function destroy(MenuItem $menuItem)
    {
        // بررسی وجود زیرمنوها
        if ($menuItem->children()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف آیتم منوی دارای زیرمنو وجود ندارد. ابتدا زیرمنوها را حذف کنید.');
        }

        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'آیتم منو با موفقیت حذف شد.');
    }

    public function togglePublish(MenuItem $menuItem)
    {
        $menuItem->update(['publish' => !$menuItem->publish]);
        $status = $menuItem->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "آیتم منو {$status} شد.");
    }

    public function reorder(Request $request)
    {
        $orderData = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'exists:menu_items,id',
            'order.*.order' => 'integer|min:0',
        ]);

        foreach ($orderData['order'] as $item) {
            MenuItem::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'ترتیب منو با موفقیت به‌روز شد.']);
    }

    public function getChildren($parentId)
    {
        $children = MenuItem::where('parent_id', $parentId)
            ->where('publish', true)
            ->orderBy('order')
            ->get();

        return response()->json($children);
    }
}
