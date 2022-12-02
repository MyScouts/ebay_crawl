@if ($logged_in_user)
    <x-utils.form-button :action="route('admin.products.unPublishProduct', ['productId' => $product->id])" method="patch" button-class="btn btn-danger btn-sm mt-2" icon="cil-trash"
        name="confirm-item" >
        @lang('Delete')
    </x-utils.form-button>
@endif
