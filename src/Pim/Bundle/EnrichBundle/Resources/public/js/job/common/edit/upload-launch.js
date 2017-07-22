
/**
 * Upload and launch button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseLaunch from 'pim/job/common/edit/launch'
import router from 'pim/router'
import messenger from 'oro/messenger'
export default BaseLaunch.extend({
  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:job:file_updated', this.render.bind(this))

    return BaseLaunch.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inherit}
   */
  launch: function () {
    if (this.getFormData().file) {
      var formData = new FormData()
      formData.append('file', this.getFormData().file)

      router.showLoadingMask()

      $.ajax({
        url: this.getUrl(),
        method: 'POST',
        data: formData,
        contentType: false,
        cache: false,
        processData: false
      })
        .then(function (response) {
          router.redirect(response.redirectUrl)
        })
        .fail(function () {
          messenger.notify('error', __('pim_enrich.form.job_instance.fail.launch'))
        })
        .always(router.hideLoadingMask())
    }
  },

  /**
   * {@inherit}
   */
  isVisible: function () {
    return $.Deferred().resolve(this.getFormData().file).promise()
  }
})
