<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        $announcements = Announcement::query()
            ->with('author')
            ->latest()
            ->paginate(15);

        return view('announcements.index', ['announcements' => $announcements]);
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('announcements.form', ['announcement' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        Announcement::create([
            ...$this->validated($request),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('announcements.index')->with('message', 'Pengumuman baru berhasil dipublikasikan.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorizeAdmin();

        return view('announcements.form', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeAdmin();

        $announcement->update($this->validated($request));

        return redirect()->route('announcements.index')->with('message', 'Pengumuman berhasil diperbarui.');
    }

    public function togglePublished(Announcement $announcement): RedirectResponse
    {
        $this->authorizeAdmin();

        $announcement->update(['is_published' => ! $announcement->is_published]);

        return redirect()->route('announcements.index')
            ->with('message', $announcement->is_published ? 'Pengumuman ditampilkan lagi ke publik.' : 'Pengumuman disembunyikan dari publik.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizeAdmin();

        $announcement->delete();

        return redirect()->route('announcements.index')->with('message', "\"{$announcement->title}\" berhasil dihapus.");
    }

    /**
     * Daftar pengumuman yang sudah dipublikasikan — halaman publik, tanpa login.
     */
    public function publicIndex(): View
    {
        $announcements = Announcement::query()
            ->where('is_published', true)
            ->latest()
            ->paginate(10);

        return view('public.announcements', ['announcements' => $announcements]);
    }

    /**
     * Detail satu pengumuman — halaman publik, tanpa login. Pengumuman yang disembunyikan
     * (is_published=false) sengaja 404 walau tahu ID-nya, biar tidak bisa diakses lewat link lama.
     */
    public function publicShow(Announcement $announcement): View
    {
        abort_unless($announcement->is_published, 404);

        return view('public.announcement-show', ['announcement' => $announcement]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang bisa mengelola pengumuman.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
