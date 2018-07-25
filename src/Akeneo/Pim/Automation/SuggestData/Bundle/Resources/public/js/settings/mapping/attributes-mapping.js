'use strict';

/**
 * This module will allow user to map the attributes from PIM.ai to the catalog attributes.
 * It displays a grid with all the attributes to map.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/user-context',
    'pim/i18n',
    'pimee/template/settings/mapping/attributes-mapping',
], function (
        _,
        __,
        BaseForm,
        UserContext,
        i18n,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html('');

                const familyMapping = this.getFormData();
                if (familyMapping.hasOwnProperty('mapping') && familyMapping.mapping.length) {
                    const mapping = familyMapping.mapping;
                    const locale = UserContext.get('uiLocale');
                    const statuses = {
                        0: __(this.config.labels.pending),
                        1: __(this.config.labels.active),
                        2: __(this.config.labels.inactive)
                    };
                    this.$el.html(this.template({
                        mapping,
                        locale,
                        statuses,
                        i18n,
                        pim_ai_attribute: __(this.config.labels.pim_ai_attribute),
                        catalog_attribute: __(this.config.labels.catalog_attribute),
                        suggest_data: __(this.config.labels.suggest_data)
                    }));
                }

                return this;
            }
        })
    }
);
