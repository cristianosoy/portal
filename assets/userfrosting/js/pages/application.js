/**
 * Page-specific Javascript file. Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/application') | raw }}
 *
 * Target page: application
 */
$(document).ready(function () {
    // Apply select2 to fields
    $('.js-select2').select2();

    // Workaround for checkbox problem in some browsers
    $('#input-tos_accepted').on('ifChecked', function (event) {
        $(this).val('1');
    });

    // Set up form for submission
    $('#application-form').ufForm({
        validators: page.validators,
        msgTarget: $('#alerts-page')
    }).on('submitSuccess.ufForm', function () {
        // Reload page on success
        window.location.reload();
    });

    // Open TOS modal window
    $('#open-tos').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/applications/tos',
            msgTarget: $('#alerts-page')
        });
    });

    // Open confirm deletion modal window
    $('#open-confirm-delete').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/applications/confirm-delete',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        $('body').on('renderSuccess.ufModal', function (data) {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
                .on('submitSuccess.ufForm', function () {
                    // Reload page on success
                    window.location.reload();
                });
        });
    });
});
