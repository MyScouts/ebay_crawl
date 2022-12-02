@extends('backend.layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <x-forms.patch :action="route('admin.setting.update')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Settings')
            </x-slot>

            <x-slot name="body">
                @if (isset($settings) && count($settings) > 0)
                    @foreach ($settings as $setting)
                        <div class="form-group row">
                            <label for="name" class="col-md-2 col-form-label">@lang($setting->name)</label>
                            <div class="col-md-10">
                                <input type="text" name="{{ $setting->key }}" class="form-control"
                                    placeholder="{{ __($setting->name) }}" value="{{ old($setting->key) ?? $setting->value }}"
                                    required />
                            </div>
                        </div>
                    @endforeach
                @endif
                <!--form-group-->
            </x-slot>
            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update Setting')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection
