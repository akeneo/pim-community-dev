'use strict';

/**
 * This module displays a button to subscribe a family to PIM.ai.
 * If the family is subscribed, it displays only a button to show the user the family is subscribed.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pimee/template/settings/mapping/subscribe',
], function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            events: {
                'click .subscribe': () => {
                    this.setData({
                        enabled: true
                    });
                    // TODO Make a POST to save the model
                    this.getRoot().render();
                }
            },

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
                if (familyMapping.hasOwnProperty('enabled')) {
                    this.$el.html(this.template({
                        enabled: familyMapping.enabled,
                        subscribeLabel: __(this.config.labels.subscribe),
                        subscribedLabel: __(this.config.labels.subscribed)
                    }));
                }


                return this;
            }
        })
    }
);
