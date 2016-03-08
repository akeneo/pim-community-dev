'use strict';

define(
    ['underscore', 'pim/controller/form', 'jquery.select2'],
    function (_, FormController) {
        return FormController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function () {
                return FormController.prototype.renderRoute.apply(this, arguments).then(_.bind(function () {
                    if (!this.active) {
                        return;
                    }

                    this.$('select').select2();
                }, this));
            }
        });
    }
);
