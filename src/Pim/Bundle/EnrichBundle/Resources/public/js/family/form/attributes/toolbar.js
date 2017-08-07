'use strict';

/**
 * Attributes tab top toolbar view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/family/tab/attributes/toolbar'
    ],
    function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknGridToolbar',
            template: _.template(template),
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:update_read_only', function (readOnly) {
                    this.readOnly = readOnly;

                    this.render();
                }.bind(this));

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                if (this.readOnly) {
                    this.$el.empty();

                    return this;
                }

                this.$el.html(this.template({}));

                this.renderExtensions();
            }
        });
    }
);
