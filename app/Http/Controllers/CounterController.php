<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        $counters = Counter::latest()
            ->paginate(10);

        $publishedCount = Counter::where('publish', true)->count();

        return view('admin.counters.index', compact('counters', 'publishedCount'));
    }

    public function create()
    {
        return view('admin.counters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:191',
            'number' => 'required|string|max:191',
            'title' => 'required|string|max:191',
            'publish' => 'sometimes|boolean',
        ]);

        $validated['publish'] = $request->has('publish');

        Counter::create($validated);

        return redirect()->route('admin.counters.index')
            ->with('success', 'شمارنده با موفقیت ایجاد شد.');
    }

    public function show(Counter $counter)
    {
        return view('admin.counters.show', compact('counter'));
    }

    public function edit(Counter $counter)
    {
        return view('admin.counters.edit', compact('counter'));
    }

    public function update(Request $request, Counter $counter)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:191',
            'number' => 'required|string|max:191',
            'title' => 'required|string|max:191',
            'publish' => 'sometimes|boolean',
        ]);

        $validated['publish'] = $request->has('publish');

        $counter->update($validated);

        return redirect()->route('admin.counters.index')
            ->with('success', 'شمارنده با موفقیت ویرایش شد.');
    }

    public function destroy(Counter $counter)
    {
        $counter->delete();

        return redirect()->route('admin.counters.index')
            ->with('success', 'شمارنده با موفقیت حذف شد.');
    }

    public function togglePublish(Counter $counter)
    {
        $counter->update(['publish' => !$counter->publish]);
        $status = $counter->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "شمارنده {$status} شد.");
    }

    public function published()
    {
        $counters = Counter::where('publish', true)
            ->latest()
            ->get();

        return view('admin.counters.published', compact('counters'));
    }
}
