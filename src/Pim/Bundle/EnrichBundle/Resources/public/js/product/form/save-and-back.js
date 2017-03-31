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
        'oro/translator',
        'oro/mediator',
        'pim/form',
        'pim/router',
        'routing',
        'oro/messenger'
    ],
    function (
        $,
        _,
        __,
        mediator,
        BaseForm,
        router,
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
                        'click .save-product-and-back': this.saveAndBack.bind(this)
                    }
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            saveAndBack: function () {
                this.parent.getExtension('save')
                    .save({silent: true})
                    .done(function () {
                        messenger.enqueueMessage(
                            'success',
                            __('pim_enrich.entity.product.info.update_successful')
                        );
                        router.redirectToRoute('pim_enrich_product_index');
                    }.bind(this))
                    .fail(function () {
                        messenger.notify(
                            'error',
                            __('pim_enrich.entity.product.info.update_failed')
                        );
                    });
            }
        });
    }
);
