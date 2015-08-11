'use strict';
/**
 * Save extension that adds a save draft button if ownership rights are not granted
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product-edit-form/save',
        'pim/fetcher-registry'
    ],
    function ($, _, Save, FetcherRegistry) {
        return Save.extend({
            render: function () {
                var categories = this.getFormData().categories;
                var isOwner = this.getFormData().meta.is_owner;

                if (!isOwner) {
                    this.updateSuccessMessage = _.__('pimee_enrich.entity.product_draft.info.update_successful');
                    this.updateFailureMessage = _.__('pimee_enrich.entity.product_draft.info.update_failed');

                    if ('save-buttons' in this.parent.extensions) {
                        var buttons = this.parent.extensions['save-buttons'].model.get('buttons');
                        var saveButton = _.findWhere(buttons, {className: 'save-product'});
                        if (saveButton) {
                            saveButton.label = _.__('pimee_enrich.entity.product.btn.save_draft');
                        }
                    }
                }

                return Save.prototype.render.apply(this, arguments);
            }
        });
    }
);
