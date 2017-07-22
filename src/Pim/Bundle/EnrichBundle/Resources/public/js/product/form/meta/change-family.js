/**
 * Change family extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import Backbone from 'backbone'
import BaseForm from 'pim/form'
import FetcherRegistry from 'pim/fetcher-registry'
import ProductManager from 'pim/product-manager'
import modalTemplate from 'pim/template/product/meta/change-family-modal'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import Routing from 'routing'
import initSelect2 from 'pim/initselect2'
import 'bootstrap-modal'
import 'jquery.select2'

export default BaseForm.extend({
  tagName: 'i',
  className: 'icon-pencil change-family AknTitleContainer-metaLink',
  modalTemplate: _.template(modalTemplate),
  events: {
    'click': 'showModal'
  },
  render: function () {
    this.delegateEvents()

    return BaseForm.prototype.render.apply(this, arguments)
  },
  showModal: function () {
    var familyModal = new Backbone.BootstrapModal({
      allowCancel: true,
      cancelText: _.__('pim_enrich.entity.product.meta.groups.modal.close'),
      title: _.__('pim_enrich.form.product.change_family.modal.title'),
      content: this.modalTemplate({
        product: this.getFormData()
      })
    })

    familyModal.on('ok', function () {
      var selectedFamily = familyModal.$('.family-select2').select2('val') || null

      this.getFormModel().set('family', selectedFamily)
      ProductManager.generateMissing(this.getFormData()).then(function (product) {
        this.getRoot().trigger('pim_enrich:form:change-family:before')

        this.setData(product)

        this.getRoot().trigger('pim_enrich:form:change-family:after')
        familyModal.close()
      }.bind(this))
    }.bind(this))

    familyModal.open()
    var self = this

    var options = {
      allowClear: true,
      ajax: {
        url: Routing.generate('pim_enrich_family_rest_index'),
        quietMillis: 250,
        cache: true,
        data: function (term, page) {
          return {
            search: term,
            options: {
              limit: 20,
              page: page,
              locale: UserContext.get('catalogLocale')
            }
          }
        },
        results: function (families) {
          var data = {
            more: _.keys(families).length === 20,
            results: []
          }
          _.each(families, function (value, key) {
            data.results.push({
              id: key,
              text: i18n.getLabel(value.labels, UserContext.get('catalogLocale'), value.code)
            })
          })

          return data
        }
      },
      initSelection: function (element, callback) {
        var productFamily = self.getFormData().family
        if (productFamily !== null) {
          FetcherRegistry.getFetcher('family')
            .fetch(self.getFormData().family)
            .then(function (family) {
              callback({
                id: family.code,
                text: i18n.getLabel(
                  family.labels,
                  UserContext.get('catalogLocale'),
                  family.code
                )
              })
            })
        }
      }
    }

    initSelect2.init(familyModal.$('.family-select2'), options).select2('val', [])
  }
})
