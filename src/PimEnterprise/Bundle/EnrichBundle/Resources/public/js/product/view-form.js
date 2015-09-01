'use strict';
/**
 * View form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'module',
        'jquery',
        'underscore',
        'backbone',
        'pim/product-edit-form',
        'pim/form'
    ],
    function (
        module,
        $,
        _,
        Backbone,
        EditForm,
        BaseForm
    ) {
        return EditForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(module.config(), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config()['forwarded-events']);
                }

                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Add read only on fields
             *
             * @param {Event} event
             */
            addFieldExtension: function (event) {
                event.promises.push($.Deferred().resolve(event.field.setEditable(false)));
            }
        });
    }
);
