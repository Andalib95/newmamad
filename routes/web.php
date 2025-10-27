<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FaqCategoryController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MobileMenuItemController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;


//// Routes عمومی (فرانت-اند)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Routes احراز هویت
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Routes مدیریت (Admin) - با پیشوند admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {

    // داشبورد
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/quick-stats', [DashboardController::class, 'quickStats'])->name('quick-stats');
    Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('chart-data');
    Route::get('/system-info', [DashboardController::class, 'systemInfo'])->name('system-info');
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity'])->name('recent-activity');
    Route::post('/clear-cache', [DashboardController::class, 'clearCache'])->name('clear-cache');
    Route::post('/backup-database', [DashboardController::class, 'backupDatabase'])->name('backup-database');

    // مدیریت مقالات
    Route::resource('articles', ArticleController::class);
    Route::post('articles/{article}/publish', [ArticleController::class, 'publish'])->name('articles.publish');
    Route::post('articles/{article}/unpublish', [ArticleController::class, 'unpublish'])->name('articles.unpublish');

    // مدیریت دسته‌بندی‌ها
    Route::resource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle-publish', [CategoryController::class, 'togglePublish'])->name('categories.toggle-publish');

    // مدیریت تگ‌ها
    Route::resource('tags', TagController::class);

    // مدیریت نظرات
    Route::resource('comments', CommentController::class);
    Route::get('comments/pending', [CommentController::class, 'pending'])->name('comments.pending');
    Route::post('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
    Route::post('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
    Route::post('comments/{comment}/mark-read', [CommentController::class, 'markAsRead'])->name('comments.mark-read');
    Route::post('comments/{comment}/mark-unread', [CommentController::class, 'markAsUnread'])->name('comments.mark-unread');

    // مدیریت سوالات متداول
    Route::resource('faq-categories', FaqCategoryController::class);
    Route::post('faq-categories/{faqCategory}/toggle-publish', [FaqCategoryController::class, 'togglePublish'])->name('faq-categories.toggle-publish');

    Route::resource('faqs', FaqController::class);
    Route::post('faqs/{faq}/toggle-publish', [FaqController::class, 'togglePublish'])->name('faqs.toggle-publish');
    Route::post('faqs/{faq}/toggle-home', [FaqController::class, 'toggleHome'])->name('faqs.toggle-home');

    // مدیریت تماس‌ها
    Route::resource('contacts', ContactController::class);
    Route::get('contacts/unread', [ContactController::class, 'unreadMessages'])->name('contacts.unread');
    Route::post('contacts/{contact}/mark-read', [ContactController::class, 'markAsRead'])->name('contacts.mark-read');
    Route::post('contacts/{contact}/mark-unread', [ContactController::class, 'markAsUnread'])->name('contacts.mark-unread');

    // مدیریت شمارنده‌ها
    Route::resource('counters', CounterController::class);
    Route::post('counters/{counter}/toggle-publish', [CounterController::class, 'togglePublish'])->name('counters.toggle-publish');
    Route::get('counters/published', [CounterController::class, 'published'])->name('counters.published');

    // مدیریت تنظیمات
    Route::resource('configs', ConfigController::class);
    Route::get('configs/all', [ConfigController::class, 'getAllConfigs'])->name('configs.all');
    Route::post('configs/quick-update', [ConfigController::class, 'quickUpdate'])->name('configs.quick-update');

    // مدیریت خدمات
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::post('service-categories/{serviceCategory}/toggle-publish', [ServiceCategoryController::class, 'togglePublish'])->name('service-categories.toggle-publish');
    Route::post('service-categories/reorder', [ServiceCategoryController::class, 'reorder'])->name('service-categories.reorder');

    Route::resource('services', ServiceController::class);
    Route::post('services/{service}/toggle-publish', [ServiceController::class, 'togglePublish'])->name('services.toggle-publish');
    Route::get('services/published', [ServiceController::class, 'published'])->name('services.published');

    // مدیریت منوها
    Route::resource('menu-items', MenuItemController::class);
    Route::post('menu-items/{menuItem}/toggle-publish', [MenuItemController::class, 'togglePublish'])->name('menu-items.toggle-publish');
    Route::post('menu-items/reorder', [MenuItemController::class, 'reorder'])->name('menu-items.reorder');
    Route::get('menu-items/{parentId}/children', [MenuItemController::class, 'getChildren'])->name('menu-items.children');

    Route::resource('mobile-menu-items', MobileMenuItemController::class);
    Route::post('mobile-menu-items/{mobileMenuItem}/toggle-publish', [MobileMenuItemController::class, 'togglePublish'])->name('mobile-menu-items.toggle-publish');
    Route::post('mobile-menu-items/reorder', [MobileMenuItemController::class, 'reorder'])->name('mobile-menu-items.reorder');

    // مدیریت اسلایدها
    Route::resource('slides', SlideController::class);
    Route::post('slides/{slide}/toggle-publish', [SlideController::class, 'togglePublish'])->name('slides.toggle-publish');
    Route::post('slides/reorder', [SlideController::class, 'reorder'])->name('slides.reorder');
    Route::get('slides/published', [SlideController::class, 'published'])->name('slides.published');

    // مدیریت تیم
    Route::resource('teams', TeamController::class);
    Route::post('teams/{team}/toggle-publish', [TeamController::class, 'togglePublish'])->name('teams.toggle-publish');
    Route::post('teams/reorder', [TeamController::class, 'reorder'])->name('teams.reorder');
    Route::get('teams/published', [TeamController::class, 'published'])->name('teams.published');

    // مدیریت ویدئوها
    Route::resource('videos', VideoController::class);
    Route::post('videos/{video}/toggle-publish', [VideoController::class, 'togglePublish'])->name('videos.toggle-publish');
    Route::post('videos/{video}/increment-views', [VideoController::class, 'incrementViews'])->name('videos.increment-views');
    Route::post('videos/reorder', [VideoController::class, 'reorder'])->name('videos.reorder');
    Route::get('videos/published', [VideoController::class, 'published'])->name('videos.published');

    // مدیریت ریدایرکت‌ها
    Route::resource('redirects', RedirectController::class);
    Route::post('redirects/{redirect}/toggle-publish', [RedirectController::class, 'togglePublish'])->name('redirects.toggle-publish');
    Route::post('redirects/{redirect}/increment-hits', [RedirectController::class, 'incrementHits'])->name('redirects.increment-hits');
    Route::post('redirects/{redirect}/reset-hits', [RedirectController::class, 'resetHits'])->name('redirects.reset-hits');
    Route::get('redirects/active', [RedirectController::class, 'active'])->name('redirects.active');
    Route::get('redirects/most-popular', [RedirectController::class, 'mostPopular'])->name('redirects.most-popular');
    Route::get('redirects/active-list', [RedirectController::class, 'getActiveRedirects'])->name('redirects.active-list');

    // مدیریت کاربران
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('profile', [UserController::class, 'profile'])->name('users.profile');
    Route::post('profile', [UserController::class, 'updateProfile'])->name('users.update-profile');

    // مدیریت نقش‌ها و دسترسی‌ها
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/permissions', [RoleController::class, 'getRolePermissions'])->name('roles.permissions');
    Route::post('roles/{role}/assign-permission', [RoleController::class, 'assignPermission'])->name('roles.assign-permission');
    Route::post('roles/{role}/revoke-permission', [RoleController::class, 'revokePermission'])->name('roles.revoke-permission');

    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/sync', [PermissionController::class, 'syncPermissions'])->name('permissions.sync');
});

// Routes API برای فرانت-اند
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/articles', [ArticleController::class, 'apiIndex'])->name('articles.index');
    Route::get('/articles/{article}', [ArticleController::class, 'apiShow'])->name('articles.show');
    Route::get('/categories', [CategoryController::class, 'apiIndex'])->name('categories.index');
    Route::get('/services', [ServiceController::class, 'apiIndex'])->name('services.index');
    Route::get('/faqs', [FaqController::class, 'apiIndex'])->name('faqs.index');
    Route::get('/team', [TeamController::class, 'apiIndex'])->name('team.index');
    Route::post('/contact', [ContactController::class, 'apiStore'])->name('contact.store');
    Route::get('/counters', [CounterController::class, 'apiIndex'])->name('counters.index');
    Route::get('/videos', [VideoController::class, 'apiIndex'])->name('videos.index');
});

// Route برای احراز هویت لاراول
//require __DIR__ . '/auth.php';
