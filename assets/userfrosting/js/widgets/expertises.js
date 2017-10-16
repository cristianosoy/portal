/**
 * Expertises widget. Sets up dropdowns, modals, etc for a table of expertises.
 */

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachExpertiseForm() {
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
 * Link expertise action buttons, for example in a table or on a specific expertise's page.
 */
function bindExpertiseButtons(el) {
    /**
     * Buttons that launch a modal dialog
     */
    // Edit button
    el.find('.js-expertise-edit').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/expertises/edit',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachExpertiseForm();
    });

    // Delete button
    el.find('.js-expertise-delete').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/expertises/confirm-delete',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachExpertiseForm();
    });
}

function bindExpertiseCreationButton(el) {
    // Link create button
    el.find('.js-expertise-create').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/expertises/create',
            msgTarget: $('#alerts-page')
        });

        attachExpertiseForm();
    });
}
