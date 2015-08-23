'use strict';
/**
 * Save and back to the grid extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                this.trigger('save-buttons:register-button', {
                    className: 'save-product-and-back',
                    priority: 150,
                    label: _.__('pim_enrich.entity.product.btn.save_and_back'),
                    events: {
                        'click .save-product-and-back': this.saveAndCreate.bind(this)
                    }
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            saveAndCreate: function () {
                this.parent.getExtension('save')
                    .save({silent: true})
                    .done(function () {
                        messenger.addMessage(
                            'success',
                            _.__('pim_enrich.entity.product.info.update_successful'),
                            {hashNavEnabled: true}
                        );
                        var navigation = Navigation.getInstance();
                        navigation.setLocation(Routing.generate('pim_enrich_product_index'));
                    }.bind(this))
                    .fail(function () {
                        messenger.notificationFlashMessage(
                            'error',
                            _.__('pim_enrich.entity.product.info.update_failed')
                        );
                    });
            }
        });
    }
);
