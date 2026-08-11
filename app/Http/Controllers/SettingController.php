<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'kopLogo' => Setting::get('bast_kop_logo'),
            'kopBaris1' => Setting::get('bast_kop_baris1', 'Fakultas Matematika dan Ilmu Pengetahuan Alam'),
            'kopBaris2' => Setting::get('bast_kop_baris2', 'Universitas Sebelas Maret'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bast_kop_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bast_kop_baris1' => 'nullable|string|max:255',
            'bast_kop_baris2' => 'nullable|string|max:255',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('bast_kop_logo')) {
            $path = $request->file('bast_kop_logo')->store('settings', 'public');
            Setting::set('bast_kop_logo', $path);
        } elseif ($request->boolean('remove_logo')) {
            Setting::set('bast_kop_logo', null);
        }

        Setting::set('bast_kop_baris1', $data['bast_kop_baris1'] ?? null);
        Setting::set('bast_kop_baris2', $data['bast_kop_baris2'] ?? null);

        return redirect()->route('settings.edit')->with('message', 'Pengaturan kop surat berhasil disimpan.');
    }
}
