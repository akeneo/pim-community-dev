
/**
 * Main image extension
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseForm from 'pim/form'
import template from 'pim/template/form/main-image'
import MediaUrlGenerator from 'pim/media-url-generator'
export default BaseForm.extend({
  className: 'AknTitleContainer-image',
  template: _.template(template),

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
    this.$el.empty().append(this.template({
      path: this.getPath()
    }))

    return BaseForm.prototype.render.apply(this, arguments)
  },

  /**
   * Returns the path of the image to display
   *
   * @returns {string}
   */
  getPath: function () {
    if (undefined !== this.config.path) {
      return this.config.path
    }

    var filePath = _.result(this.getFormData().meta.image, 'filePath', null)

    return MediaUrlGenerator.getMediaShowUrl(filePath, 'thumbnail_small')
  }
})
