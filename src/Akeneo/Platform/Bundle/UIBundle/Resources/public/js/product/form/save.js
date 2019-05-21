'use strict';
/**
 * Save extension to adapt messages if ownership rights are not granted
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product-edit-form/save'
    ],
    function ($, _, Save) {
        return Save.extend({
            render: function () {
                var isOwner = this.getFormData().meta.is_owner;

                if (!isOwner) {
                    this.updateSuccessMessage = _.__('pimee_enrich.entity.product_draft.flash.update.success');
                    this.updateFailureMessage = _.__('pimee_enrich.entity.product_draft.flash.update.fail');
                }

                return Save.prototype.render.apply(this, arguments);
            }
        });
    }
);
