@if ($logged_in_user)
    {{-- <x-utils.form-button :action="route('admin.dashboard')" method="patch" button-class="btn btn-primary btn-sm" icon="cil-x-circle"
        name="confirm-item">
        @lang('Cancle')
    </x-utils.form-button> --}}
    <x-utils.form-button :action="route('admin.products.unPublishProduct', ['productId' => $product->id])" method="patch" button-class="btn btn-danger btn-sm mt-2" icon="cil-trash"
        name="confirm-item" {{-- permission="admin.access.user.reactivate" --}}>
        @lang('Delete')
    </x-utils.form-button>
@endif
