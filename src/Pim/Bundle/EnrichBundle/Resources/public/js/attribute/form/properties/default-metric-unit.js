/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseField from 'pim/attribute-edit-form/properties/field'
import fetcherRegistry from 'pim/fetcher-registry'
import template from 'pim/template/attribute/tab/properties/select'
export default BaseField.extend({
  template: _.template(template),
  measures: {},

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      BaseField.prototype.configure.apply(this, arguments),
      fetcherRegistry.getFetcher('measure').fetchAll()
        .then(function (measures) {
          this.measures = measures
        }.bind(this))
    )
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    var metricFamily = this.getFormData().metric_family

    return this.template(_.extend(templateContext, {
      value: this.getFormData()[this.fieldName],
      choices: this.formatChoices(this.measures[metricFamily].units),
      multiple: false,
      labels: {
        defaultLabel: __('pim_enrich.form.attribute.tab.properties.default_label.default_metric_unit')
      }
    }))
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('select.select2').select2()
  },

  /**
   * {@inheritdoc}
   *
   * This field shouldn't be displayed if the attribute has no metric family defined yet.
   */
  isVisible: function () {
    return undefined !== this.getFormData().metric_family && this.getFormData().metric_family !== null
  },

  /**
   * Transforms:
   *
   * {
   *     BIT: {...},
   *     BYTE: {...}
   * }
   *
   * into:
   *
   * {
   *     BIT: "Bit",
   *     BYTE: "Octet"
   * }
   *
   * (for locale fr_FR)
   *
   * @param {Object} units
   */
  formatChoices: function (units) {
    var unitCodes = _.keys(units)

    return _.object(
      unitCodes,
      _.map(unitCodes, __)
    )
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val()
  }
})
