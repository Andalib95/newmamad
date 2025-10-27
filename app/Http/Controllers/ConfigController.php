<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    public function index()
    {
        $configs = Config::latest()
            ->paginate(10);

        return view('admin.configs.index', compact('configs'));
    }

    public function create()
    {
        return view('admin.configs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191|unique:configs',
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        // تبدیل داده به JSON
        $validated['data'] = json_encode([
            'value' => $validated['value'],
            'description' => $validated['description'] ?? null
        ]);

        Config::create($validated);

        // پاک کردن کش
        Cache::forget('app_configs');

        return redirect()->route('admin.configs.index')
            ->with('success', 'تنظیمات با موفقیت ایجاد شد.');
    }

    public function show(Config $config)
    {
        $configData = json_decode($config->data, true);
        return view('admin.configs.show', compact('config', 'configData'));
    }

    public function edit(Config $config)
    {
        $configData = json_decode($config->data, true);
        return view('admin.configs.edit', compact('config', 'configData'));
    }

    public function update(Request $request, Config $config)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191|unique:configs,key,' . $config->id,
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        // تبدیل داده به JSON
        $validated['data'] = json_encode([
            'value' => $validated['value'],
            'description' => $validated['description'] ?? null
        ]);

        $config->update($validated);

        // پاک کردن کش
        Cache::forget('app_configs');

        return redirect()->route('admin.configs.index')
            ->with('success', 'تنظیمات با موفقیت ویرایش شد.');
    }

    public function destroy(Config $config)
    {
        $config->delete();

        // پاک کردن کش
        Cache::forget('app_configs');

        return redirect()->route('admin.configs.index')
            ->with('success', 'تنظیمات با موفقیت حذف شد.');
    }

    // متد برای دریافت تمام تنظیمات
    public function getAllConfigs()
    {
        $configs = Cache::remember('app_configs', 3600, function () {
            return Config::all()->mapWithKeys(function ($config) {
                $data = json_decode($config->data, true);
                return [$config->key => $data['value']];
            });
        });

        return response()->json($configs);
    }

    // متد برای به‌روزرسانی سریع تنظیمات
    public function quickUpdate(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $config = Config::where('key', $validated['key'])->first();

        if ($config) {
            $data = json_decode($config->data, true);
            $data['value'] = $validated['value'];
            $config->update(['data' => json_encode($data)]);

            // پاک کردن کش
            Cache::forget('app_configs');

            return response()->json(['success' => true, 'message' => 'تنظیمات به‌روز شد.']);
        }

        return response()->json(['success' => false, 'message' => 'تنظیمات یافت نشد.'], 404);
    }
}
