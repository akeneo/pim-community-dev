
import _ from 'underscore'
import __ from 'oro/translator'
import BaseFilter from 'pim/filter/filter'
import Routing from 'routing'
import template from 'pim/template/filter/product/family'
import fetcherRegistry from 'pim/fetcher-registry'
import userContext from 'pim/user-context'
import i18n from 'pim/i18n'
import 'jquery.select2'
export default BaseFilter.extend({
  shortname: 'family',
  config: {},
  template: _.template(template),
  events: {
    'change [name="filter-value"]': 'updateState'
  },

        /**
         * {@inheritdoc}
         */
  initialize: function (config) {
    this.config = config.config

    this.selectOptions = {
      allowClear: true,
      multiple: true,
      ajax: {
        url: Routing.generate(this.config.url),
        quietMillis: 250,
        cache: true,
        data: function (term, page) {
          return {
            search: term,
            options: {
              limit: 20,
              page: page,
              locale: userContext.get('uiLocale')
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
              text: i18n.getLabel(value.labels, userContext.get('uiLocale'), value.code)
            })
          })

          return data
        }
      },
      initSelection: function (element, callback) {
        var families = this.getValue()
        if (families !== null) {
          fetcherRegistry.getFetcher('family')
                            .fetchByIdentifiers(families)
                            .then(function (families) {
                              callback(_.map(families, function (family) {
                                return {
                                  id: family.code,
                                  text: i18n.getLabel(family.labels, userContext.get('uiLocale'), family.code)
                                }
                              }))
                            })
        }
      }.bind(this)
    }

    return BaseFilter.prototype.initialize.apply(this, arguments)
  },

        /**
         * {@inherit}
         */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
      _.defaults(data, {field: this.getCode(), operator: '='})
    }.bind(this))

    return BaseFilter.prototype.configure.apply(this, arguments)
  },

        /**
         * {@inheritdoc}
         */
  renderInput: function () {
    return this.template({
      isEditable: this.isEditable(),
      __: __,
      field: this.getField(),
      value: this.getValue(),
      shortname: this.shortname
    })
  },

        /**
         * {@inheritdoc}
         */
  postRender: function () {
    this.$('[name="filter-value"]').select2(this.selectOptions)
  },

        /**
         * {@inheritdoc}
         */
  isEmpty: function () {
    return _.isEmpty(this.getValue())
  },

        /**
         * {@inheritdoc}
         */
  updateState: function () {
    var value = this.$('[name="filter-value"]').val()

    this.setData({
      field: this.getField(),
      operator: 'IN',
      value: value === ''
                    ? []
                    : value.split(',')
    })
  }
})
