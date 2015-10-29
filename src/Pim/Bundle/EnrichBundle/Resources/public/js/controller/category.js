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
            /**
             * {@inheritdoc}
             */
            afterSubmit: function (xhr) {
                this.$('#category-form').html(xhr.responseText);
                mediator.trigger('route_complete pim:reinit');
                router.hideLoadingMask();
            }
        });
    }
);
