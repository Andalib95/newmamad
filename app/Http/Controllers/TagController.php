<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('articles')
            ->latest()
            ->paginate(15);

        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'titre' => 'nullable|string|max:191',
            'alias' => 'required|string|max:191|unique:tags',
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
        ]);

        // ایجاد alias خودکار
        if (empty($validated['alias'])) {
            $validated['alias'] = Str::slug($validated['title']);
        }

        // مقادیر boolean
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'تگ با موفقیت ایجاد شد.');
    }

    public function show(Tag $tag)
    {
        $tag->load(['articles' => function($query) {
            $query->where('publish', true)->latest();
        }]);
        return view('admin.tags.show', compact('tag'));
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'titre' => 'nullable|string|max:191',
            'alias' => 'required|string|max:191|unique:tags,alias,' . $tag->id,
            'index' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
        ]);

        // مقادیر boolean
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'تگ با موفقیت ویرایش شد.');
    }

    public function destroy(Tag $tag)
    {
        // حذف روابط
        $tag->articles()->detach();
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'تگ با موفقیت حذف شد.');
    }
}
