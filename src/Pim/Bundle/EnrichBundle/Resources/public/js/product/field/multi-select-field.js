/**
 * Multi select field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import Field from 'pim/field'
import _ from 'underscore'
import fieldTemplate from 'pim/template/product/field/multi-select'
import Routing from 'routing'
import createOption from 'pim/attribute-option/create'
import SecurityContext from 'pim/security-context'
import initSelect2 from 'pim/initselect2'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import AttributeManager from 'pim/attribute-manager'

export default Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  choicePromise: null,
  promiseIdentifiers: null,
  events: {
    'change .field-input:first input.select-field': 'updateModel',
    'click .add-attribute-option': 'createOption'
  },

  /**
   * {@inheritdoc}
   */
  getTemplateContext: function () {
    return Field.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
      var isAllowed = SecurityContext.isGranted('pim_enrich_attribute_edit')
      templateContext.userCanAddOption = this.editable && isAllowed

      return templateContext
    }.bind(this))
  },

  /**
   * Create a new option for this multi select field
   */
  createOption: function () {
    if (!SecurityContext.isGranted('pim_enrich_attribute_edit')) {
      return
    }
    createOption(this.attribute).then(function (option) {
      if (this.isEditable()) {
        var value = this.getCurrentValue().data
        value.push(option.code)
        this.setCurrentValue(value)
      }

      this.choicePromise = null
      this.render()
    }.bind(this))
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (context) {
    return this.fieldTemplate(context)
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('[data-toggle="tooltip"]').tooltip()
    this.getChoiceUrl().then(function (choiceUrl) {
      var options = {
        ajax: {
          url: choiceUrl,
          quietMillis: 250,
          cache: true,
          data: function (term, page) {
            return {
              search: term,
              options: {
                limit: 20,
                page: page
              }
            }
          },
          results: function (response) {
            if (response.results) {
              response.more = _.keys(response.results).length === 20

              return response
            }

            var data = {
              more: _.keys(response).length === 20,
              results: []
            }
            _.each(response, function (value) {
              data.results.push(this.convertBackendItem(value))
            }.bind(this))

            return data
          }.bind(this)
        },
        initSelection: function (element, callback) {
          var identifiers = AttributeManager.getValue(
            this.model.attributes.values,
            this.attribute,
            UserContext.get('catalogLocale'),
            UserContext.get('catalogScope')
          ).data

          if (this.choicePromise === null || this.promiseIdentifiers !== identifiers) {
            this.choicePromise = $.get(choiceUrl, {
              options: {
                identifiers: identifiers
              }
            })
            this.promiseIdentifiers = identifiers
          }

          this.choicePromise.then(function (results) {
            if (_.has(results, 'results')) {
              results = results.results
            }

            var choices = _.map($(element).val().split(','), function (choice) {
              var option = _.findWhere(results, {
                code: choice
              })
              if (option) {
                return this.convertBackendItem(option)
              }

              return _.findWhere(results, {
                id: choice
              })
            }.bind(this))
            callback(_.compact(choices))
          }.bind(this))
        }.bind(this),
        multiple: true
      }

      initSelect2.init(this.$('input.select-field'), options)
    }.bind(this))
  },

  /**
   * Get the URL to retrieve the choice list for this select field
   *
   * @returns {Promise}
   */
  getChoiceUrl: function () {
    return $.Deferred().resolve(
      Routing.generate(
        'pim_enrich_attributeoption_get',
        {
          identifier: this.attribute.code
        }
      )
    ).promise()
  },

  /**
   * {@inheritdoc}
   */
  updateModel: function () {
    var data = this.$('.field-input:first input.select-field').val().split(',')
    if (data.length === 1 && data[0] === '') {
      data = []
    }

    this.choicePromise = null

    this.setCurrentValue(data)
  },

  /**
   * Convert the item returned from the backend to fit select2 needs
   *
   * @param {object} item
   *
   * @return {object}
   */
  convertBackendItem: function (item) {
    return {
      id: item.code,
      text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code)
    }
  }
})
