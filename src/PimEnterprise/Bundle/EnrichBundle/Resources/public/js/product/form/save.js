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
        'pimee/permission-manager'
    ],
    function ($, _, Save, PermissionManager) {
        return Save.extend({
            permissions: {},
            configure: function () {
                return $.when(
                    PermissionManager.getPermissions().then(_.bind(function (permissions) {
                        this.permissions = permissions;
                    }, this)),
                    Save.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                var categories = this.getData().categories;
                var isOwner = !categories.length ||
                    !!_.intersection(this.permissions.categories.OWN_PRODUCTS, categories).length;

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
