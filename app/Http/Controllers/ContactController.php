<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()
            ->paginate(20);

        $unreadCount = Contact::where('read', false)->count();

        return view('admin.contacts.index', compact('contacts', 'unreadCount'));
    }

    public function create()
    {
        return view('admin.contacts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191|regex:/^[0-9+\-\s()]+$/',
            'email' => 'required|email|max:191',
            'message' => 'required|string|min:10|max:1000',
        ]);

        Contact::create($validated);

        // ارسال ایمیل اطلاع‌رسانی (اختیاری)
        // $this->sendNotificationEmail($validated);

        return redirect()->route('admin.contacts.index')
            ->with('success', 'پیام تماس با موفقیت ثبت شد.');
    }

    public function show(Contact $contact)
    {
        // علامت‌گذاری به عنوان خوانده شده
        if (!$contact->read) {
            $contact->update(['read' => true]);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        return view('admin.contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191|regex:/^[0-9+\-\s()]+$/',
            'email' => 'required|email|max:191',
            'message' => 'required|string|min:10|max:1000',
        ]);

        $contact->update($validated);

        return redirect()->route('admin.contacts.index')
            ->with('success', 'پیام تماس با موفقیت ویرایش شد.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')
            ->with('success', 'پیام تماس با موفقیت حذف شد.');
    }

    // متدهای اضافی برای مدیریت پیام‌ها
    public function markAsRead(Contact $contact)
    {
        $contact->update(['read' => true]);
        return redirect()->back()->with('success', 'پیام به عنوان خوانده شده علامت‌گذاری شد.');
    }

    public function markAsUnread(Contact $contact)
    {
        $contact->update(['read' => false]);
        return redirect()->back()->with('success', 'پیام به عنوان خوانده نشده علامت‌گذاری شد.');
    }

    public function unreadMessages()
    {
        $contacts = Contact::where('read', false)
            ->latest()
            ->paginate(20);

        return view('admin.contacts.unread', compact('contacts'));
    }

    // ارسال ایمیل اطلاع‌رسانی (اختیاری)
    private function sendNotificationEmail($contactData)
    {
        // Mail::to('admin@example.com')->send(new ContactNotification($contactData));
    }
}
