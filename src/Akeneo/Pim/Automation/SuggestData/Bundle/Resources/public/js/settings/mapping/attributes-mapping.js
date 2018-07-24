'use strict';

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define([
    'underscore',
    'pim/form',
    'pim/user-context',
    'pim/i18n',
    'pimee/template/settings/mapping/attributes-mapping',
], function (
        _,
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
            render() {
                this.$el.html('');

                const familyMapping = this.getFormData();
                if (familyMapping.hasOwnProperty('mapping')) {
                    const mapping = familyMapping.mapping;
                    const locale = UserContext.get('uiLocale');
                    const statuses = {
                        0: 'pending', // TODO To translate
                        1: 'active',
                        2: 'inactive'
                    };
                    this.$el.html(this.template({
                        mapping,
                        locale,
                        statuses,
                        i18n
                    }));
                }

                return this;
            }
        })
    }
);
