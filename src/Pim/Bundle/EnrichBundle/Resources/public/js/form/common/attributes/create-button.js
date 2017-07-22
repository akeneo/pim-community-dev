
/**
 * Create attribute button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import BaseForm from 'pim/form'
import template from 'pim/template/form/tab/attribute/create-button'
import templateModal from 'pim/template/form/tab/attribute/create-modal-content'
import Routing from 'routing'
import FetcherRegistry from 'pim/fetcher-registry'
import router from 'pim/router'
import 'bootstrap-modal'
export default BaseForm.extend({
  template: _.template(template),
  templateModal: _.template(templateModal),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config

    BaseForm.prototype.initialize.apply(this, arguments)
  },

  /**
   * Create the dialog modal and bind clicks
   */
  createModal: function (attributeTypesMap) {
    var attributeTypes = this.formatAndSortAttributeTypesByLabel(attributeTypesMap)

    var moduleConfig = __moduleConfig

    var modal = null
    var modalContent = this.templateModal({
      attributeTypes: attributeTypes,
      iconsMap: moduleConfig.attribute_icons,
      generateRoute: function (route, params) {
        return Routing.generate(route, params)
      }
    })

    $('#attribute-create-button').on('click', function () {
      if (modal) {
        modal.open()
      } else {
        modal = new Backbone.BootstrapModal({
          title: __(this.config.modalTitle),
          content: modalContent
        })

        modal.open()
        modal.$el.find('.modal-footer').remove()

        modal.$el.on('click', '.attribute-choice', function () {
          modal.close()
          modal.$el.remove()
          router.redirect($(this).attr('data-route'), {
            trigger: true
          })
        })
      }
    }.bind(this))
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    FetcherRegistry.getFetcher('attribute-type')
      .fetchAll()
      .then(function (attributeTypes) {
        this.$el.html(this.template({
          buttonTitle: __(this.config.buttonTitle)
        }))

        this.createModal(attributeTypes)
      }.bind(this))

    return this
  },

  /**
   * Format the map to an array and sort attributeTypes by label
   * @param attributeTypesMap
   * @returns {Array}
   */
  formatAndSortAttributeTypesByLabel: function (attributeTypesMap) {
    var sortedAttributeTypesByLabel = []
    for (var key in attributeTypesMap) {
      if (attributeTypesMap.hasOwnProperty(key)) {
        sortedAttributeTypesByLabel.push({
          code: key,
          label: __('pim_enrich.entity.attribute.type.' + attributeTypesMap[key])
        })
      }
    }

    sortedAttributeTypesByLabel.sort(function (a, b) {
      return a.label.localeCompare(b.label)
    })

    return sortedAttributeTypesByLabel
  }
})
