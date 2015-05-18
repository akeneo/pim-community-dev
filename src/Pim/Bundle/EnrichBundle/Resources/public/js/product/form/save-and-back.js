'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/form',
        'oro/navigation',
        'routing',
        'oro/messenger'
    ],
    function (
        $,
        _,
        mediator,
        BaseForm,
        Navigation,
        Routing,
        messenger
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            configure: function () {
                if ('save-buttons' in this.parent.extensions) {
                    this.parent.extensions['save-buttons'].addButton({
                        className: 'save-product-and-back',
                        priority: 150,
                        label: _.__('pim_enrich.entity.product.btn.save_and_back'),
                        events: {
                            'click .save-product-and-back': _.bind(this.saveAndCreate, this)
                        }
                    });
                }

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            saveAndCreate: function () {
                this.parent.extensions.save.save({ silent: true }).done(_.bind(function () {
                    messenger.addMessage(
                        'success',
                        _.__('pim_enrich.entity.product.info.update_successful'),
                        {hashNavEnabled: true}
                    );
                    var navigation = Navigation.getInstance();
                    navigation.setLocation(Routing.generate('pim_enrich_product_index'));
                }, this));
            }
        });
    }
);
