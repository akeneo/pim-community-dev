/**
 * Product groups extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import BaseForm from 'pim/form'
import Routing from 'routing'
import formTemplate from 'pim/template/product/meta/groups'
import modalTemplate from 'pim/template/product/meta/group-modal'
import UserContext from 'pim/user-context'
import FetcherRegistry from 'pim/fetcher-registry'
import GroupManager from 'pim/group-manager'
import router from 'pim/router'
import i18n from 'pim/i18n'
import LoadingMask from 'oro/loading-mask'
import 'bootstrap-modal'

export default BaseForm.extend({
  tagName: 'span',

  className: 'AknColumn-block product-groups',

  template: _.template(formTemplate),

  modalTemplate: _.template(modalTemplate),

  events: {
    'click a[data-group]': 'displayModal'
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render)

    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this
    }

    GroupManager.getProductGroups(this.getFormData()).done(function (groups) {
      groups = this.prepareGroupsForTemplate(groups)

      if (groups.length) {
        this.$el.html(this.template({
          label: __('pim_enrich.entity.product.meta.groups.title'),
          groups: groups
        }))
      } else {
        this.$el.html('')
      }
    }.bind(this))

    return this
  },

  /**
   * Prepare groups for being displayed in the template
   *
   * @param {Array} groups
   *
   * @returns {Array}
   */
  prepareGroupsForTemplate: function (groups) {
    var locale = UserContext.get('catalogLocale')

    return _.map(groups, function (group) {
      return {
        label: group.labels[locale] || '[' + group.code + ']',
        code: group.code,
        isVariant: group.type === 'VARIANT'
      }
    })
  },

  /**
   * Get the product list for the given group
   *
   * @param {integer} groupCode
   *
   * @returns {Array}
   */
  getProductList: function (groupCode) {
    return $.getJSON(Routing.generate('pim_enrich_group_rest_list_products', {
      identifier: groupCode
    })).then(_.identity)
  },

  /**
   * Show the modal which displays infos about produt groups
   *
   * @param {Object} event
   */
  displayModal: function (event) {
    var loadingMask = new LoadingMask()
    loadingMask.render().$el.appendTo(this.getRoot().$el).show()

    GroupManager.getProductGroups(this.getFormData()).done(function (groups) {
      var group = _.findWhere(groups, {
        code: event.currentTarget.dataset.group
      })

      $.when(this.getProductList(group.code), FetcherRegistry.getFetcher('attribute')
        .getIdentifierAttribute()).done(function (productList, identifierAttribute) {
          loadingMask.remove()
          this.groupModal = new Backbone.BootstrapModal({
            allowCancel: true,
            okText: __('pim_enrich.entity.product.meta.groups.modal.view_group'),
            cancelText: __('pim_enrich.entity.product.meta.groups.modal.close'),
            title: __('pim_enrich.entity.product.meta.groups.modal.title', {
              group: i18n.getLabel(group.labels, UserContext.get('catalogLocale'), group.code)
            }),
            content: this.modalTemplate({
              products: productList.products,
              productCount: productList.productCount,
              identifier: identifierAttribute,
              locale: UserContext.get('catalogLocale')
            })
          })

          this.groupModal.on('ok', function visitGroup () {
            this.groupModal.close()
            var route = group.type === 'VARIANT'
            ? 'pim_enrich_variant_group_edit'
            : 'pim_enrich_group_edit'
            var parameters = {
              code: group.code
            }

            router.redirectToRoute(route, parameters)
          }.bind(this))
          this.groupModal.open()

          this.groupModal.$el.on('click', 'a[data-product-id]', function visitProduct (event) {
            this.groupModal.close()
            router.redirectToRoute('pim_enrich_product_edit', {
              id: event.currentTarget.dataset.productId
            })
          }.bind(this))
        }.bind(this))
    }.bind(this))
  }
})
