/**
 * Download file extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/job-execution/download-archives-buttons'
import Routing from 'routing'
import propertyAccessor from 'pim/common/property'
import securityContext from 'pim/security-context'

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = meta.config

    BaseForm.prototype.initialize.apply(this, arguments)
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
    if (!this.isVisible()) {
      return this
    }
    var formData = this.getFormData()
    this.$el.html(this.template({
      __: __,
      archives: propertyAccessor.accessProperty(this.getFormData(), this.config.filesPath),
      executionId: formData.meta.id,
      generateRoute: this.getUrl.bind(this)
    }))

    return this
  },

  /**
   * Get the url from parameters
   *
   * @returns {string}
   */
  getUrl: function (parameters) {
    return Routing.generate(
      this.config.url,
      parameters
    )
  },

  /**
   * Returns true if the extension should be visible
   *
   * @returns {boolean}
   */
  isVisible: function () {
    var formData = this.getFormData()
    if (formData.jobInstance.type === 'export') {
      return securityContext.isGranted(this.config.aclIdExport)
    } else if (formData.jobInstance.type === 'import') {
      return securityContext.isGranted(this.config.aclIdImport)
    } else {
      return true
    }
  }
})
