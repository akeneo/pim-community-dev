"use strict";

define([
        'module',
        'routing',
        'pim/form/common/save'
    ],
    function(
        module,
        Routing,
        SaveForm
    ) {
        return SaveForm.extend({

            /**
             * {@inheritdoc}
             */
            getSaveUrl: function () {
                return Routing.generate(module.config().route);
            },

            /**
             * {@inheritdoc}
             */
            postSave: function (data) {
                this.setData(data);
                this.getRoot().trigger('pim_enrich:form:entity:post_fetch');
                SaveForm.prototype.postSave.apply(this, arguments);
            }
        });
    }
);
