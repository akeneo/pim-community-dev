/**
 * Special view that serves as a bridge between its parent and another tree.
 * It builds a tree on-the-fly at configure time then adds it to its own children. The result is a fully functional
 * tree as if it was build "statically".
 * The goal is to build modular view trees without duplicating a bunch of conf.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

import $ from 'jquery'
import _ from 'underscore'
import Backbone from 'backbone'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import FormBuilder from 'pim/form-builder'
import FormRegistry from 'pim/attribute-edit-form/type-specific-form-registry'
export default BaseForm.extend({
  config: {},

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
  configure: function () {
    var formName = FormRegistry.getFormName(this.getRoot().getType(), this.config.mode)

    if (undefined !== formName && formName !== null) {
      return FormBuilder.buildForm(formName)
        .then(function (form) {
          this.addExtension(
            form.code,
            form,
            'self',
            100
          )

          return BaseForm.prototype.configure.apply(this)
        }.bind(this))
    }

    return BaseForm.prototype.configure.apply(this)
  }
})
