'use strict';

define(
    ['jquery', 'underscore', 'pim/controller/base', 'pim/router'],
    function ($, _, BaseController, router) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route, path) {
                return $.get(path).then(() => {
                    window.location = router.generate('pim_user_security_login');
                }).promise();
            }
        });
    }
);
