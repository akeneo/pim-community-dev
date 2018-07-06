'use strict';

/**
 * Identifier mapping
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pimee/template/settings/mapping/identifiers'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        i18n,
        UserContext,
        fetcherRegistry,
        template
    ) {
        const identifiers = [
            {'label':__('akeneo_suggest_data.settings.index.tab.identifiers.headers.brand_label')},
            {'label':'MPN'},
            {'label':'UPC'},
            {'label':'ASIN'},
        ];

        const headers = {
            'identifiersLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.identifiers_label'),
            'attributeGroupLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.attribute_group_label'),
            'attributeLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.attribute_label'),
            'suggestDataLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.suggest_data_label'),
        };

        return BaseForm.extend({
            events: {},
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template({
                    headers: headers,
                    identifiers: identifiers,
                }));

                this.renderExtensions();

                this.delegateEvents();

                return this;
            },

        });
    }
);
