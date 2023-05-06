<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::select('value', 'key', 'name')->get();
        return view('backend.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $settings = $request->only(Setting::select('key')->pluck('key')->toArray());
            $data = [];
            foreach ($settings as $key => $value) {
                Setting::where('key', $key)->select('name')->update([
                    'value' => $value,
                ]);
            }

            Setting::upsert($data, ['key']);
        } catch (\Throwable $th) {
            Log::error("Update Settings", ['error' => $th->getMessage()]);
            DB::rollBack();
            throw new GeneralException(__("Settings isn't update!"));
        }
        DB::commit();
        return redirect()->route('admin.setting.index')->withFlashSuccess(__('Settings was successfully updated.'));
    }
}
