'use strict';

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define([
    'underscore',
    'pim/form',
    'pimee/template/settings/mapping/subscribe',
], function (
        _,
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
                    // Make a POST to save the model
                    this.getRoot().render();
                }
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html('');
                const familyMapping = this.getFormData();
                if (familyMapping.hasOwnProperty('enabled')) {
                    this.$el.html(this.template({
                        enabled: familyMapping.enabled
                    }));
                }


                return this;
            }
        })
    }
);
