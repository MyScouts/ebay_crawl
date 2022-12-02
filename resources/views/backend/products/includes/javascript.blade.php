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

        $('body').keydown(function(e) {
            console.log("🚀 ~ file: javascript.blade.php:17 ~ $ ~ e.which == 13", e.which)
            switch (e.which) {
                case 37:
                    const description = $('#saveContent').val();
                    if (description.trim() <= 0) return;
                    $('.form-publish').trigger('submit');
                    e.preventDefault();
                    break;

                case 39:
                    $('.btn-next')[0].click();
                    e.preventDefault();
                    break;

                case 40:
                    $('.btn-delete')[0].click();
                    e.preventDefault();
                    break;

                default:
                    break;
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
