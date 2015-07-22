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
        'pim/form',
        'pim/router',
        'oro/messenger'
    ],
    function (
        $,
        _,
        BaseForm,
        router,
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
                this.parent.extensions.save
                    .save({silent: true})
                    .done(_.bind(function () {
                        messenger.addMessage(
                            'success',
                            _.__('pim_enrich.entity.product.info.update_successful'),
                            {hashNavEnabled: true}
                        );
                        router.redirectToRoute('pim_enrich_product_index');
                    }, this))
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
