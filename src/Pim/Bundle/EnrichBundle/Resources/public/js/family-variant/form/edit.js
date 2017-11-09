'use strict';

/**
 * Family variant form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form/common/edit-form',
        'pim/i18n',
        'pim/user-context',
        'pim/template/family-variant/edit'
    ],
    function (
        _,
        __,
        BaseEdit,
        i18n,
        userContext,
        template
    ) {
        return BaseEdit.extend({
            template: _.template(template),
            events: {
                'click .cancel': function () {
                    this.trigger('cancel');
                }
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.getRoot().trigger('pim_enrich:form:render:before');

                this.$el.html(this.template({
                    familyVariant: this.getFormData(),
                    __: __,
                    i18n,
                    userContext,
                }));

                this.renderExtensions();

                this.getRoot().trigger('pim_enrich:form:render:after');
            }
        });
    }
);
