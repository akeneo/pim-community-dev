define(
    [
        'jquery',
        'pim/controller/front',
        'oro/mediator',
        'pim/product/grid/bridge'
    ],
    function (
        $,
        BaseController,
        mediator,
        bridge
    ) {
        return BaseController.extend({
            /**
            * {@inheritdoc}
            */
            initialize() {
                mediator.trigger('pim_menu:highlight:tab', { extension: 'pim-menu-products' });

                return BaseController.prototype.initialize.apply(this, arguments);
            },

            /**
            * {@inheritdoc}
            */
            renderForm() {
                setTimeout(() => bridge.default(this.el));

                return $.Deferred().resolve();
            }
        });
    }
);
