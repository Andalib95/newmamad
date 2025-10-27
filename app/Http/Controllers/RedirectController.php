<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::latest()
            ->paginate(20);

        $activeCount = Redirect::where('publish', true)->count();

        return view('admin.redirects.index', compact('redirects', 'activeCount'));
    }

    public function create()
    {
        return view('admin.redirects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|string|max:191|unique:redirects',
            'to' => 'required|string|max:191',
            'status_code' => 'required|in:301,302,307,308',
            'publish' => 'sometimes|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['publish'] = $request->has('publish');
        $validated['hits'] = 0;

        Redirect::create($validated);

        // پاک کردن کش ریدایرکت‌ها
        Cache::forget('active_redirects');

        return redirect()->route('admin.redirects.index')
            ->with('success', 'ریدایرکت با موفقیت ایجاد شد.');
    }

    public function show(Redirect $redirect)
    {
        return view('admin.redirects.show', compact('redirect'));
    }

    public function edit(Redirect $redirect)
    {
        return view('admin.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect)
    {
        $validated = $request->validate([
            'from' => 'required|string|max:191|unique:redirects,from,' . $redirect->id,
            'to' => 'required|string|max:191',
            'status_code' => 'required|in:301,302,307,308',
            'publish' => 'sometimes|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['publish'] = $request->has('publish');

        $redirect->update($validated);

        // پاک کردن کش ریدایرکت‌ها
        Cache::forget('active_redirects');

        return redirect()->route('admin.redirects.index')
            ->with('success', 'ریدایرکت با موفقیت ویرایش شد.');
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();

        // پاک کردن کش ریدایرکت‌ها
        Cache::forget('active_redirects');

        return redirect()->route('admin.redirects.index')
            ->with('success', 'ریدایرکت با موفقیت حذف شد.');
    }

    public function togglePublish(Redirect $redirect)
    {
        $redirect->update(['publish' => !$redirect->publish]);

        // پاک کردن کش ریدایرکت‌ها
        Cache::forget('active_redirects');

        $status = $redirect->publish ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "ریدایرکت {$status} شد.");
    }

    public function incrementHits(Redirect $redirect)
    {
        $redirect->increment('hits');
        return response()->json(['hits' => $redirect->hits]);
    }

    public function resetHits(Redirect $redirect)
    {
        $redirect->update(['hits' => 0]);
        return redirect()->back()->with('success', 'تعداد بازدیدهای ریدایرکت بازنشانی شد.');
    }

    public function active()
    {
        $redirects = Redirect::where('publish', true)
            ->orderBy('hits', 'desc')
            ->get();

        return view('admin.redirects.active', compact('redirects'));
    }

    public function mostPopular()
    {
        $redirects = Redirect::where('publish', true)
            ->orderBy('hits', 'desc')
            ->limit(10)
            ->get();

        return view('admin.redirects.most-popular', compact('redirects'));
    }

    // متد برای دریافت ریدایرکت‌های فعال (برای استفاده در میدلور)
    public function getActiveRedirects()
    {
        $redirects = Cache::remember('active_redirects', 3600, function () {
            return Redirect::where('publish', true)
                ->get()
                ->mapWithKeys(function ($redirect) {
                    return [$redirect->from => [
                        'to' => $redirect->to,
                        'status_code' => $redirect->status_code
                    ]];
                })
                ->toArray();
        });

        return response()->json($redirects);
    }
}
