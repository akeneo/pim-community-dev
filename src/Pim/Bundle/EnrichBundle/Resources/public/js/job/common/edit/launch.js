
/**
 * Launch button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import Routing from 'routing'
import router from 'pim/router'
import propertyAccessor from 'pim/common/property'
import messenger from 'oro/messenger'
import template from 'pim/template/export/common/edit/launch'
export default BaseForm.extend({
  template: _.template(template),
  events: {
    'click .AknButton': 'launch'
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config

    BaseForm.prototype.initialize.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.isVisible().then(function (isVisible) {
      if (!isVisible) {
        return this
      }

      this.$el.html(this.template({
        label: __(this.config.label)
      }))
    }.bind(this))

    this.delegateEvents()

    return this
  },

  /**
   * Launch the job
   */
  launch: function () {
    $.post(this.getUrl())
      .then(function (response) {
        router.redirect(response.redirectUrl)
      })
      .fail(function () {
        messenger.notify('error', __('pim_enrich.form.job_instance.fail.launch'))
      })
  },

  /**
   * Get the route to launch the job
   *
   * @return {string}
   */
  getUrl: function () {
    var params = {}
    params[this.config.identifier.name] = propertyAccessor.accessProperty(
      this.getFormData(),
      this.config.identifier.path
    )

    return Routing.generate(this.config.route, params)
  },

  /**
   * Should this extension render
   *
   * @return {Promise}
   */
  isVisible: function () {
    return $.Deferred().resolve(true).promise()
  }
})
