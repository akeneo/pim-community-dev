'use strict';

/**
 * This module will display tabs. Contrary to the other 'tabs' module, this one does not load
 * the tab content in the render. Each click on a tab will call the Router to load a new URL.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/router',
        'pimee/template/settings/mapping/tabs'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        Router,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click .tab-link': function (event) {
                    Router.redirectToRoute($(event.currentTarget).data('route'));
                }
            },

            /**
             * {@inheritdoc}
             */
            initialize(meta) {
                BaseForm.prototype.initialize.apply(this, arguments);

                this.config = meta.config;
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    tabs: this.config.tabs,
                    selected: this.config.selected,
                    __
                }));

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);
