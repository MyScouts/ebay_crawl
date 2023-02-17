@extends('backend.layouts.app')

@section('title', __('User Management'))

@section('breadcrumb-links')
    @include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')
    <x-backend.card>
        @include('backend.products.includes.publish-product-card')
    </x-backend.card>

    <x-backend.card>
        <x-slot name="header">
            @lang('Product Management')
        </x-slot>
        
        <x-slot name="headerActions">

            <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#exampleModal">
                <i class="c-icon cil-fax"></i>
                Export data
            </button>

            <x-utils.link icon="c-icon cil-sync" class="btn btn-info text-white text-sm" :href="route('admin.products.list')"
                :text="__('Reload')" />
        </x-slot>

        <x-slot name="body">
            <?php 
                $phone_numbers = "490984904390";
                if(strpos($phone_numbers,'490') == 0)
                {
                    echo "49".substr($phone_numbers,3,14);
                }    
                else {
                    echo "false";
                }
            ?>
            <livewire:backend.products-table />
        </x-slot>
    </x-backend.card>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.products.exportProduct') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Choose time export</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div data-coreui-locale="en-US" data-coreui-toggle="date-picker" id="start-date"
                                    data-coreui-placeholder="{{ __('Start Date') }}"></div>
                            </div>
                            <div class="col-lg-6">
                                <div data-coreui-locale="en-US" data-coreui-toggle="date-picker" id="end-date"
                                    data-coreui-placeholder="{{ __('End Date') }}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('backend.products.includes.javascript')
