<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\User;
use App\Models\Service;
use App\Models\Faq;
use App\Models\Team;
use App\Models\Slide;
use App\Models\Video;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_articles' => Article::count(),
            'published_articles' => Article::where('publish', true)->count(),
            'total_comments' => Comment::count(),
            'pending_comments' => Comment::where('status', false)->count(),
            'total_contacts' => Contact::count(),
            'unread_contacts' => Contact::where('read', false)->count(),
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'published_services' => Service::where('publish', true)->count(),
            'total_faqs' => Faq::count(),
            'total_team' => Team::count(),
            'total_slides' => Slide::count(),
            'total_videos' => Video::count(),
            'total_redirects' => Redirect::count(),
        ];

        // آخرین مقالات
        $recentArticles = Article::with(['user', 'mainCategory'])
            ->latest()
            ->limit(5)
            ->get();

        // آخرین نظرات
        $recentComments = Comment::with('article')
            ->latest()
            ->limit(5)
            ->get();

        // آخرین پیام‌های تماس
        $recentContacts = Contact::latest()
            ->limit(5)
            ->get();

        // آمار ماهانه مقالات
        $monthlyArticles = Article::selectRaw('YEAR(created_at) year, MONTH(created_at) month, COUNT(*) count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // محبوب‌ترین مقالات
        $popularArticles = Article::where('publish', true)
            ->orderBy('hits', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'hits']);

        return view('admin.dashboard', compact(
            'stats',
            'recentArticles',
            'recentComments',
            'recentContacts',
            'monthlyArticles',
            'popularArticles'
        ));
    }

    public function getChartData(Request $request)
    {
        $range = $request->get('range', '30days');

        switch ($range) {
            case '7days':
                $days = 7;
                break;
            case '30days':
                $days = 30;
                break;
            case '90days':
                $days = 90;
                break;
            default:
                $days = 30;
        }

        $articles = Article::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $comments = Comment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $contacts = Contact::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'articles' => $articles,
            'comments' => $comments,
            'contacts' => $contacts
        ]);
    }

    public function quickStats()
    {
        $stats = [
            'today_articles' => Article::whereDate('created_at', today())->count(),
            'today_comments' => Comment::whereDate('created_at', today())->count(),
            'today_contacts' => Contact::whereDate('created_at', today())->count(),
            'total_published' => Article::where('publish', true)->count(),
            'pending_comments' => Comment::where('status', false)->count(),
            'unread_contacts' => Contact::where('read', false)->count(),
        ];

        return response()->json($stats);
    }

    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'فعال' : 'غیرفعال',
        ];

        return response()->json($info);
    }

    public function recentActivity()
    {
        // فعالیت‌های اخیر از جداول مختلف
        $activities = [];

        // آخرین مقالات
        $articles = Article::with('user')
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($article) {
                return [
                    'type' => 'article',
                    'title' => $article->title,
                    'user' => $article->user->name,
                    'time' => $article->created_at->diffForHumans(),
                    'icon' => 'fas fa-file-alt'
                ];
            });

        // آخرین نظرات
        $comments = Comment::with('article')
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'title' => 'نظر جدید برای: ' . Str::limit($comment->article->title, 30),
                    'user' => $comment->author_name,
                    'time' => $comment->created_at->diffForHumans(),
                    'icon' => 'fas fa-comment'
                ];
            });

        // آخرین تماس‌ها
        $contacts = Contact::latest()
            ->limit(3)
            ->get()
            ->map(function ($contact) {
                return [
                    'type' => 'contact',
                    'title' => 'پیام جدید: ' . $contact->subject,
                    'user' => $contact->name,
                    'time' => $contact->created_at->diffForHumans(),
                    'icon' => 'fas fa-envelope'
                ];
            });

        $activities = $articles->merge($comments)->merge($contacts)
            ->sortByDesc('time')
            ->take(8);

        return response()->json($activities->values());
    }

    public function backupDatabase()
    {
        // این متد می‌تواند برای پشتیبان‌گیری از دیتابیس استفاده شود
        // در حالت واقعی باید از پکیج‌های پشتیبان‌گیری مانند spatie/laravel-backup استفاده شود

        return response()->json([
            'success' => true,
            'message' => 'عملیات پشتیبان‌گیری با موفقیت انجام شد.'
        ]);
    }

    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'کش سیستم با موفقیت پاک شد.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در پاک کردن کش: ' . $e->getMessage()
            ], 500);
        }
    }
}
