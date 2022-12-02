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
                    aria-expanded="false" aria-controls="productDesc" :text="__('Read more')" icon="c-icon cil-toggle-off"
                    id="readmore-btn" />
            </x-slot>

            <x-slot name="body">
                <x-forms.patch :action="route('admin.products.publishDo', ['productId' => $publish->id])">
                    <h6 class="text-sm">
                        <span class="font-weight-bold">DETAIL URL:</span>
                        <x-utils.link :href="$publish->ebay_url" :text="$publish->ebay_url" target="_blank" />
                    </h6>
                    <div class="collapse mt-2" id="productDesc">
                        <span class="font-weight-bold">DESCIPTION:</span>
                        <span class="data-description">{!! highlightNumber($publish->description) !!}</span>
                        <div class="mb-3">
                            <label for="saveContent" class="form-label font-weight-bold">Save content:</label>
                            <textarea class="form-control" id="saveContent" rows="3" required></textarea>
                        </div>
                    </div>
                </x-forms.patch>
                <div class="text-center">
                    <x-utils.form-children :action="route('admin.products.publishDo', ['productId' => $publish->id])" method="patch" button-class="btn btn-success"
                        icon="cil-check-circle" :text="__('Save')" formClass="d-inline form-publish" id="formPublish">
                        <textarea class="form-control" name="description" id="description" rows="3" hidden></textarea>
                    </x-utils.form-children>

                    <x-utils.form-button :action="route('admin.products.unPublishProduct', ['productId' => $publish->id])" method="patch" button-class="btn btn-danger" icon="cil-trash"
                        name="confirm-item">
                        @lang('Delete')
                    </x-utils.form-button>

                    <x-utils.link :href="route('admin.products.nextProduct')" :text="__('Next') . ' (đã làm:' . $logged_in_user->totalPublish() . ' còn lại:' . $total  .')'" class="btn btn-dark" icon="cil-arrow-circle-right" />
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

@section('js-footer')
    <script>
        console.log("Time reload: " + {{ $timeReload ?? 0 }});
        let selectText = null;

        $('body').keypress(function(e) {
            if (e.which == 13) {
                const description = $('#saveContent').val();
                if (description.trim() <= 0) return;
                $('.form-publish').trigger('submit');
                return false;
            }
        });

        $('.data-description').keyup(function() {
            selectText = getSelectionText();
            if (selectText.length <= 0) return;
            $('#saveContent').val(selectText);
        });

        $('.data-description').mouseup(function() {
            selectText = getSelectionText();
            if (selectText.length <= 0) return;
            $('#saveContent').val(selectText);
        });

        $('#readmore-btn').get(0).click();

        $('.form-publish').submit(function() {
            $('#description').val($('#saveContent').val());
        });

        function getSelectionText() {
            var text = "";
            if (window.getSelection) {
                text = window.getSelection().toString();
            } else if (document.selection && document.selection.type != "Control") {
                text = document.selection.createRange().text;
            }
            return text.trim();
        }
    </script>

    @if (!empty($timeReload))
        <script>
            console.log({{ $timeReload }});
            setTimeout(() => {
                window.location.reload(true);
            }, {{ $timeReload }});
        </script>
    @endif

@endsection
