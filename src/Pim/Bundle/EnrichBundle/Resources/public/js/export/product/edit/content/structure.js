'use strict';
/**
 * Structure section
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'text!pim/template/export/product/edit/content/structure',
        'pim/form'
    ],
    function (
        _,
        __,
        template,
        BaseForm
    ) {
        return BaseForm.extend({
            className: 'structure-filters',
            errors: [],
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:export:validation_error',
                    this.setValidationErrors.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            setValidationErrors: function (errors) {
                this.errors = errors;
            },

            /**
             * Get the validtion errors for the given field
             *
             * @param {string} field
             * @param {mixed}  fallback
             *
             * @return {mixed}
             */
            getValidationErrorsForField: function (field, fallback) {
                return (undefined !== this.errors.structure && undefined !== this.errors.structure[field]) ?
                    this.errors.structure[field] :
                    fallback;
            },

            /**
             * Renders this view.
             *
             * @return {Object}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.$el.html(this.template({__: __}));

                this.renderExtensions();

                return this;
            }
        });
    }
);
