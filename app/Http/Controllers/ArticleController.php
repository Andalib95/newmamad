<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['user', 'mainCategory', 'categories', 'tags', 'comments'])
            ->latest()
            ->paginate(10);

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $users = User::all();
        $categories = Category::where('publish', true)->get();
        $tags = Tag::all();

        return view('admin.articles.create', compact('users', 'categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'main_category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:articles',
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'intro_text' => 'nullable|string',
            'body' => 'required|string',
            'publish' => 'sometimes|boolean',
            'comment_status' => 'sometimes|boolean',
            'index' => 'sometimes|boolean',
            'has_toc' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'sometimes|array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // ایجاد alias خودکار
        if (empty($validated['alias'])) {
            $validated['alias'] = Str::slug($validated['title']);
        }

        // مقادیر پیش‌فرض برای فیلدهای boolean
        $validated['publish'] = $request->has('publish');
        $validated['comment_status'] = $request->has('comment_status');
        $validated['index'] = $request->has('index');
        $validated['has_toc'] = $request->has('has_toc');
        $validated['follow'] = $request->has('follow');

        $article = Article::create($validated);

        // اضافه کردن دسته‌بندی‌ها و تگ‌ها
        if ($request->has('categories')) {
            $article->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $article->tags()->sync($request->tags);
        }

        // آپلود تصویر شاخص
        if ($request->hasFile('featured_image')) {
            $article->addMedia($request->file('featured_image'))
                ->toMediaCollection('featured_image');
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'مقاله با موفقیت ایجاد شد.');
    }

    public function show(Article $article)
    {
        $article->load(['user', 'mainCategory', 'categories', 'tags', 'comments']);
        return view('admin.articles.show', compact('article'));
    }

    public function edit(Article $article)
    {
        $users = User::all();
        $categories = Category::where('publish', true)->get();
        $tags = Tag::all();
        $article->load(['categories', 'tags']);

        return view('admin.articles.edit', compact('article', 'users', 'categories', 'tags'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'main_category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:191',
            'alias' => 'required|string|max:191|unique:articles,alias,' . $article->id,
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'intro_text' => 'nullable|string',
            'body' => 'required|string',
            'publish' => 'sometimes|boolean',
            'comment_status' => 'sometimes|boolean',
            'index' => 'sometimes|boolean',
            'has_toc' => 'sometimes|boolean',
            'follow' => 'sometimes|boolean',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'sometimes|array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // مقادیر boolean
        $validated['publish'] = $request->has('publish');
        $validated['comment_status'] = $request->has('comment_status');
        $validated['index'] = $request->has('index');
        $validated['has_toc'] = $request->has('has_toc');
        $validated['follow'] = $request->has('follow');

        $article->update($validated);

        // به‌روزرسانی روابط
        if ($request->has('categories')) {
            $article->categories()->sync($request->categories);
        } else {
            $article->categories()->detach();
        }

        if ($request->has('tags')) {
            $article->tags()->sync($request->tags);
        } else {
            $article->tags()->detach();
        }

        // آپلود تصویر شاخص
        if ($request->hasFile('featured_image')) {
            $article->clearMediaCollection('featured_image');
            $article->addMedia($request->file('featured_image'))
                ->toMediaCollection('featured_image');
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'مقاله با موفقیت ویرایش شد.');
    }

    public function destroy(Article $article)
    {
        // حذف روابط
        $article->categories()->detach();
        $article->tags()->detach();

        // حذف رسانه‌ها
        $article->clearMediaCollection('featured_image');

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'مقاله با موفقیت حذف شد.');
    }

    // متدهای اضافی برای مدیریت وضعیت
    public function publish(Article $article)
    {
        $article->update(['publish' => true]);
        return redirect()->back()->with('success', 'مقاله منتشر شد.');
    }

    public function unpublish(Article $article)
    {
        $article->update(['publish' => false]);
        return redirect()->back()->with('success', 'مقاله از حالت انتشار خارج شد.');
    }
}
