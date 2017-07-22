/**
 * Family save extension
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseSave from 'pim/form/common/save'
import messenger from 'oro/messenger'
import FamilySaver from 'pim/saver/family'
import FieldManager from 'pim/field-manager'
import i18n from 'pim/i18n'
import UserContext from 'pim/user-context'
import 'pim/security-context'

export default BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.family.info.update_successful'),
  updateFailureMessage: __('pim_enrich.entity.family.info.update_failed'),

  /**
   * {@inheritdoc}
   */
  save: function () {
    var family = $.extend(true, {}, this.getFormData())
    family.attributes = _.pluck(family.attributes, 'code')

    delete family.meta

    var notReadyFields = FieldManager.getNotReadyFields()
    if (notReadyFields.length > 0) {
      var fieldLabels = _.map(notReadyFields, function (field) {
        return i18n.getLabel(
          field.attribute.label,
          UserContext.get('catalogLocale'),
          field.attribute.code
        )
      })

      messenger.notify(
        'error',
        __(
          'pim_enrich.entity.family.info.field_not_ready',
          {
            'fields': fieldLabels.join(', ')
          }
        )
      )

      return
    }

    this.showLoadingMask()
    this.getRoot().trigger('pim_enrich:form:entity:pre_save')

    return FamilySaver
      .save(family.code, family, 'PUT')
      .then(function (data) {
        this.postSave()

        this.setData(data)
        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data)
      }.bind(this))
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this))
  }
})
