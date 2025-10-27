<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('article')
            ->latest()
            ->paginate(20);

        $pendingCount = Comment::where('status', false)->count();
        $approvedCount = Comment::where('status', true)->count();

        return view('admin.comments.index', compact('comments', 'pendingCount', 'approvedCount'));
    }

    public function create()
    {
        $articles = Article::where('publish', true)->where('comment_status', true)->get();
        return view('admin.comments.create', compact('articles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'author_name' => 'required|string|max:191',
            'author_email' => 'required|email|max:191',
            'body' => 'required|string|min:10',
            'answer' => 'nullable|string',
            'status' => 'sometimes|boolean',
        ]);

        // افزودن اطلاعات سیستم
        $validated['author_ip'] = $request->ip();
        $validated['author_agent'] = $request->userAgent();
        $validated['status'] = $request->has('status');

        $comment = Comment::create($validated);

        // افزایش تعداد نظرات مقاله
        if ($validated['status']) {
            $article = Article::find($validated['article_id']);
            $article->increment('comment_count');
        }

        return redirect()->route('admin.comments.index')
            ->with('success', 'نظر با موفقیت ایجاد شد.');
    }

    public function show(Comment $comment)
    {
        $comment->load('article');
        return view('admin.comments.show', compact('comment'));
    }

    public function edit(Comment $comment)
    {
        $articles = Article::where('publish', true)->where('comment_status', true)->get();
        return view('admin.comments.edit', compact('comment', 'articles'));
    }

    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'author_name' => 'required|string|max:191',
            'author_email' => 'required|email|max:191',
            'body' => 'required|string|min:10',
            'answer' => 'nullable|string',
            'status' => 'sometimes|boolean',
        ]);

        $oldStatus = $comment->status;
        $validated['status'] = $request->has('status');

        $comment->update($validated);

        // مدیریت تعداد نظرات مقاله
        $article = Article::find($validated['article_id']);
        if ($oldStatus != $validated['status']) {
            if ($validated['status']) {
                $article->increment('comment_count');
            } else {
                if ($article->comment_count > 0) {
                    $article->decrement('comment_count');
                }
            }
        }

        return redirect()->route('admin.comments.index')
            ->with('success', 'نظر با موفقیت ویرایش شد.');
    }

    public function destroy(Comment $comment)
    {
        // کاهش تعداد نظرات مقاله
        if ($comment->status) {
            $article = Article::find($comment->article_id);
            if ($article && $article->comment_count > 0) {
                $article->decrement('comment_count');
            }
        }

        $comment->delete();

        return redirect()->route('admin.comments.index')
            ->with('success', 'نظر با موفقیت حذف شد.');
    }

    // متدهای اضافی برای مدیریت نظرات
    public function approve(Comment $comment)
    {
        $comment->update(['status' => true]);

        // افزایش تعداد نظرات مقاله
        $article = Article::find($comment->article_id);
        $article->increment('comment_count');

        return redirect()->back()->with('success', 'نظر با موفقیت تایید شد.');
    }

    public function reject(Comment $comment)
    {
        $oldStatus = $comment->status;
        $comment->update(['status' => false]);

        // کاهش تعداد نظرات مقاله
        if ($oldStatus) {
            $article = Article::find($comment->article_id);
            if ($article && $article->comment_count > 0) {
                $article->decrement('comment_count');
            }
        }

        return redirect()->back()->with('success', 'نظر با موفقیت رد شد.');
    }

    public function pending()
    {
        $comments = Comment::with('article')
            ->where('status', false)
            ->latest()
            ->paginate(20);

        return view('admin.comments.pending', compact('comments'));
    }
}
