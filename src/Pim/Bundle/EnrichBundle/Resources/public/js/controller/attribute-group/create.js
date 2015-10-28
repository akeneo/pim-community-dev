'use strict';

define([
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/controller/form',
        'pim/router',
        'jquery.form'
    ], function (
        $,
        _,
        mediator,
        FormController,
        router
    ) {
        return FormController.extend({
            afterSubmit: function (xhr) {
                router.redirectToRoute('pim_enrich_attributegroup_edit', { id: xhr.responseJSON.id });
            }
        });
    }
);
