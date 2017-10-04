/**
 * University widget. Sets up dropdowns, modals, etc for a table of universities.
 */

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachUniversityForm() {
    $('body').on('renderSuccess.ufModal', function (data) {
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
 * Link university action buttons, for example in a table or on a specific university's page.
 */
function bindUniversityButtons(el) {
    /**
     * Buttons that launch a modal dialog
     */
    // Edit button
    el.find('.js-university-edit').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/universities/edit',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachUniversityForm();
    });

    // Delete button
    el.find('.js-university-delete').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/universities/confirm-delete',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachUniversityForm();
    });
}

function bindUniversityCreationButton(el) {
    // Link create button
    el.find('.js-university-create').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/universities/create',
            msgTarget: $('#alerts-page')
        });

        attachUniversityForm();
    });
}
