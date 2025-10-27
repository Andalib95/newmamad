<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::latest()
            ->paginate(12);

        $publishedCount = Team::where('publish', true)->count();

        return view('admin.teams.index', compact('teams', 'publishedCount'));
    }

    public function create()
    {
        return view('admin.teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'position' => 'required|string|max:191',
            'intro_text' => 'required|string|max:500',
            'publish' => 'sometimes|boolean',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'social_links' => 'nullable|array',
            'avatar' => 'required|image|max:2048',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        // ذخیره لینک‌های شبکه‌های اجتماعی
        if ($request->has('social_links')) {
            $validated['social_links'] = json_encode($request->social_links);
        }

        $team = Team::create($validated);

        // آپلود آواتار
        if ($request->hasFile('avatar')) {
            $team->addMedia($request->file('avatar'))
                ->toMediaCollection('avatar');
        }

        return redirect()->route('admin.teams.index')
            ->with('success', 'عضو تیم با موفقیت ایجاد شد.');
    }

    public function show(Team $team)
    {
        return view('admin.teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        return view('admin.teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'position' => 'required|string|max:191',
            'intro_text' => 'required|string|max:500',
            'publish' => 'sometimes|boolean',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'social_links' => 'nullable|array',
            'avatar' => 'sometimes|image|max:2048',
            'order' => 'required|integer|min:0',
        ]);

        $validated['publish'] = $request->has('publish');

        // ذخیره لینک‌های شبکه‌های اجتماعی
        if ($request->has('social_links')) {
            $validated['social_links'] = json_encode($request->social_links);
        }

        $team->update($validated);

        // آپلود آواتار
        if ($request->hasFile('avatar')) {
            $team->clearMediaCollection('avatar');
            $team->addMedia($request->file('avatar'))
                ->toMediaCollection('avatar');
        }

        return redirect()->route('admin.teams.index')
            ->with('success', 'عضو تیم با موفقیت ویرایش شد.');
    }

    public function destroy(Team $team)
    {
        // حذف رسانه‌ها
        $team->clearMediaCollection('avatar');

        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'عضو تیم با موفقیت حذف شد.');
    }

    public function togglePublish(Team $team)
    {
        $team->update(['publish' => !$team->publish]);
        $status = $team->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "عضو تیم {$status} شد.");
    }

    public function reorder(Request $request)
    {
        $orderData = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'exists:teams,id',
            'order.*.order' => 'integer|min:0',
        ]);

        foreach ($orderData['order'] as $item) {
            Team::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'ترتیب اعضای تیم به‌روز شد.']);
    }

    public function published()
    {
        $teams = Team::where('publish', true)
            ->orderBy('order')
            ->get();

        return view('admin.teams.published', compact('teams'));
    }
}
