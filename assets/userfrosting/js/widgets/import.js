function bindImportUserButton(el) {
    // Link import button
    el.find('.js-user-import').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/import",
            msgTarget: $("#alerts-page")
        });

        attachUserForm();
    });
};