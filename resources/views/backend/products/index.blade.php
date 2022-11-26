@extends('backend.layouts.app')

@section('title', __('User Management'))

@section('breadcrumb-links')
    @include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')
    <x-backend.card>
        @if (isset($publish))
            <x-slot name="header">
                Publish Car #{{ $publish->ebay_id }}
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" data-coreui-toggle="collapse" href="#productDesc" role="button"
                    aria-expanded="false" aria-controls="productDesc" :text="__('Read more')" icon="c-icon cil-toggle-off" />
            </x-slot>

            <x-slot name="body">
                <h6 class="text-sm">
                    <x-utils.link :href="$publish->ebay_url" :text="$publish->ebay_url" target="_blank" />
                </h6>
                <div class="collapse mb-4" id="productDesc">{{ $publish->description }}</div>

                <div class="text-right">

                    <x-utils.form-button :action="route('admin.products.publishDo', ['productId' => $publish->id])" method="patch" button-class="btn btn-success btn-sm"
                        {{-- icon="cil-x-circle"  --}} name="confirm-item" {{-- permission="admin.access.user.reactivate" --}}>
                        @lang('Publish')
                    </x-utils.form-button>

                    <x-utils.form-button :action="route('admin.products.unPublishProduct', ['productId' => $publish->id])" method="patch" button-class="btn btn-danger btn-sm"
                        {{-- icon="cil-x-circle"  --}} name="confirm-item" {{-- permission="admin.access.user.reactivate" --}}>
                        @lang('Un Publish')
                    </x-utils.form-button>

                    <x-utils.link :href="route('admin.products.nextProduct')" :text="__('Next')" class="btn btn-dark btn-sm" />
                </div>
            </x-slot>
        @else
            <x-slot name="header">Not found product need publish!</x-slot>
        @endif
    </x-backend.card>

    <x-backend.card>
        <x-slot name="header">
            @lang('Product Management')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link icon="c-icon cil-sync" class="card-header-action btn btn-info text-dark" :href="route('admin.products.list')"
                :text="__('Reload')" />
        </x-slot>

        <x-slot name="body">
            <livewire:backend.products-table />
        </x-slot>
    </x-backend.card>
@endsection
