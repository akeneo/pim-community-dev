'use strict';

/**
 * Identifier field to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'underscore',
  'pim/form/common/creation/field',
  'pim/user-context',
  'pim/i18n',
  'oro/translator',
  'pim/fetcher-registry',
  'pim/template/product-create-error',
], function (_, FieldForm, UserContext, i18n, __, FetcherRegistry, errorTemplate) {
  return FieldForm.extend({
    errorTemplate: _.template(errorTemplate),

    /**
     * Renders the form
     *
     * @return {Promise}
     */
    render: function () {
      return this.shouldDisplay().then(shouldDisplay => {
        if (!shouldDisplay) {
          this.$el.html('');
          this.updateModel({target: {value: undefined}});

          return this;
        }

        this.updateModel({target: {value: this.getFormData()[this.identifier] || ''}});

        return FetcherRegistry.getFetcher('attribute')
          .getIdentifierAttribute()
          .then(
            function (identifier) {
              this.isReadOnly(identifier).then(isReadOnly => {
                this.$el.html(
                  this.template({
                    identifier: this.identifier,
                    isReadOnly,
                    label: i18n.getLabel(identifier.labels, UserContext.get('catalogLocale'), identifier.code),
                    requiredLabel: '',
                    errors: this.getRoot().validationErrors,
                    value: this.getFormData()[this.identifier],
                  })
                );
              });
              this.delegateEvents();

              return this;
            }.bind(this)
          )
          .fail(() => {
            this.$el.html(this.errorTemplate({message: __('pim_enrich.entity.product.flash.create.fail')}));
          });
      });
    },

    isReadOnly: async function (identifier) {
      //If we are in CE, the permission registry does not exists so the button is visible
      if (undefined === FetcherRegistry.getFetcher('permission')?.options?.urls) {
        return new Promise(resolve => resolve(false));
      }

      return FetcherRegistry.getFetcher('permission')
        .fetchAll()
        .then(permissions => {
          var permission = _.findWhere(permissions.attribute_groups, {code: identifier.group});
          if (!permission) {
            return false;
          }

          return !permission.edit;
        });
    },

    shouldDisplay: async function () {
      return new Promise(resolve => resolve(true));
    },
  });
});
