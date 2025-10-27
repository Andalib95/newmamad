<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:users',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'is_admin' => 'sometimes|boolean',
            'super_admin' => 'sometimes|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');
        $validated['is_admin'] = $request->has('is_admin');
        $validated['super_admin'] = $request->has('super_admin');

        $user = User::create($validated);

        // انتساب نقش‌ها
        $user->syncRoles($request->roles);

        // آپلود آواتار
        if ($request->hasFile('avatar')) {
            $user->addMedia($request->file('avatar'))
                ->toMediaCollection('avatar');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت ایجاد شد.');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'articles']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:users,alias,' . $user->id,
            'email' => 'required|string|email|max:191|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'is_admin' => 'sometimes|boolean',
            'super_admin' => 'sometimes|boolean',
            'avatar' => 'sometimes|image|max:2048',
        ]);

        // به‌روزرسانی رمز عبور در صورت ارائه
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');
        $validated['is_admin'] = $request->has('is_admin');
        $validated['super_admin'] = $request->has('super_admin');

        $user->update($validated);

        // به‌روزرسانی نقش‌ها
        $user->syncRoles($request->roles);

        // آپلود آواتار
        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatar');
            $user->addMedia($request->file('avatar'))
                ->toMediaCollection('avatar');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت ویرایش شد.');
    }

    public function destroy(User $user)
    {
        // بررسی وجود مقالات
        if ($user->articles()->exists()) {
            return redirect()->back()
                ->with('error', 'امکان حذف کاربر دارای مقاله وجود ندارد. ابتدا مقالات کاربر را حذف کنید.');
        }

        // حذف رسانه‌ها
        $user->clearMediaCollection('avatar');

        // حذف نقش‌ها
        $user->roles()->detach();

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت حذف شد.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "کاربر {$status} شد.");
    }

    public function profile()
    {
        $user = auth()->user();
        return view('admin.users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:users,alias,' . $user->id,
            'email' => 'required|string|email|max:191|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'avatar' => 'sometimes|image|max:2048',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // آپلود آواتار
        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatar');
            $user->addMedia($request->file('avatar'))
                ->toMediaCollection('avatar');
        }

        return redirect()->route('admin.users.profile')
            ->with('success', 'پروفایل با موفقیت به‌روز شد.');
    }
}
