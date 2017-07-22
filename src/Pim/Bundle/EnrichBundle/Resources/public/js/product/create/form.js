
import $ from 'jquery'
import _ from 'underscore'
import Backbone from 'backbone'
import Routing from 'routing'
import BaseForm from 'pim/form'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import __ from 'oro/translator'
import LoadingMask from 'oro/loading-mask'
import FetcherRegistry from 'pim/fetcher-registry'
import initSelect2 from 'pim/initselect2'
import template from 'pim/template/product-create-popin'
import errorTemplate from 'pim/template/product-create-error'
import 'jquery.select2'
export default BaseForm.extend({
  template: _.template(template),
  errorTemplate: _.template(errorTemplate),
  events: {
    'change input': 'updateModel'
  },
  validationErrors: {},

  /**
   * Configure the form
   *
   * @return {Promise}
   */
  configure: function () {
    return $.when(
      FetcherRegistry.initialize(),
      BaseForm.prototype.configure.apply(this, arguments)
    )
  },

  /**
   * Model update callback
   */
  updateModel: function () {
    this.getFormModel().set('identifier', this.$('[data-code="identifier"] input').val())
    this.getFormModel().set('family', this.$('[data-code="family"] input').select2('val'))
  },

  /**
   * Save the form content by posting it to backend
   *
   * @return {Promise}
   */
  save: function () {
    this.validationErrors = {}

    var loadingMask = new LoadingMask()
    this.$el.empty().append(loadingMask.render().$el.show())

    return $.post(Routing.generate('pim_enrich_product_rest_create'), this.getFormData())
      .fail(function (response) {
        this.validationErrors = response.responseJSON.values
        this.render()
      }.bind(this))
      .always(function () {
        loadingMask.remove()
      })
  },

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render: function () {
    if (!this.configured) {
      return this
    }

    return FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
      .then(function (identifier) {
        this.$el.html(
          this.template({
            identifier: identifier,
            labels: {
              identifier: i18n.getLabel(
                identifier.labels,
                UserContext.get('catalogLocale'),
                identifier.code
              ),
              family: __('pim_enrich.entity.product.create_popin.labels.family')
            },
            errors: this.validationErrors,
            __: __
          })
        )
        this.initSelect2()

        return this.renderExtensions()
      }.bind(this), function () {
        this.$el.html(
          this.errorTemplate({
            message: __('error.creating.product')
          })
        )
      }.bind(this))
  },

  /**
   * Init select2 family field
   */
  initSelect2: function () {
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
      }
    }

    initSelect2.init(this.$('[data-code="family"] input'), options)
  }
})
