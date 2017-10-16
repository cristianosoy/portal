/**
 * Countries widget. Sets up dropdowns, modals, etc for a table of countries.
 */

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachCountryForm() {
    $('body').on('renderSuccess.ufModal', function () {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        // Set up the form for submission
        form.ufForm().on('submitSuccess.ufForm', function () {
            // Reload page on success
            window.location.reload();
        });
    });
}

/**
 * Link country action buttons, for example in a table or on a specific country's page.
 */
function bindCountryButtons(el) {
    /**
     * Buttons that launch a modal dialog
     */
    // Edit button
    el.find('.js-country-edit').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/countries/edit',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachCountryForm();
    });

    // Delete button
    el.find('.js-country-delete').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/countries/confirm-delete',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachCountryForm();
    });
}

function bindCountryCreationButton(el) {
    // Link create button
    el.find('.js-country-create').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/countries/create',
            msgTarget: $('#alerts-page')
        });

        attachCountryForm();
    });
}
