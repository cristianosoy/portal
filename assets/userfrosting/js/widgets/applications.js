/**
 * Applications widget. Sets up dropdowns, modals, etc for a table of applications.
 */

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachApplicationForm() {
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
 * Link application action buttons, for example in a table or on a specific application's page.
 */
function bindApplicationButtons(el) {
    /**
     * Buttons that launch a modal dialog
     */
    // View button
    el.find('.js-application-view').click(function () {
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/applications/view/' + $(this).data('id'),
            msgTarget: $('#alerts-page')
        });

        attachApplicationForm();
    });
    el.find('.js-application-accept').click(function () {
        // Accept / Reject button
        acceptApplication($(this).data('id'));
    });
    el.find('.js-application-delete').click(function () {

        // Delete button
        $('body').ufModal({
            sourceUrl: site.uri.public + '/modals/applications/confirm-delete',
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $('#alerts-page')
        });

        attachApplicationForm();
    });
}

/**
 * Accept or reject a users application
 */
function acceptApplication(id) {
    var data = {};

    data[site.csrf.keys.name] = site.csrf.name;
    data[site.csrf.keys.value] = site.csrf.value;

    var url = site.uri.public + '/api/applications/a/' + id;

    $.ajax({
        type: 'PUT',
        url: url,
        data: data
    }).fail(function (response) {
        // Error messages
        if ((typeof site !== "undefined") && site.debug.ajax && response.responseText) {
            document.write(response.responseText);
            document.close();
        } else {
            console.log('Error (' + response.status + '): ' + response.responseText);
        }
    }).always(function () {
        window.location.reload();
    });
}
