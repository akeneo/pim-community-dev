const __ = require('oro/translator');
const BaseSave = require('pim/form/common/save');
const MappingSaver = require('pimee/saver/identifier-mapping');

/**
 * Save extension identifiers mapping
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MappingSave extends BaseSave {
  readonly updateSuccessMessage = __('akeneo_suggest_data.entity.identifier_mapping.flash.update.success');
  readonly updateFailureMessage = __('akeneo_suggest_data.entity.identifier_mapping.flash.update.fail');

  /**
   * {@inheritdoc}
   */
  save() {
    let identifiersMapping = $.extend(true, {}, this.getFormData());
    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');
    identifiersMapping = this.cleanMapping(identifiersMapping);

    return MappingSaver
      .save(null, identifiersMapping, 'POST')
      .then((savedMapping: string) => {
        this.postSave();
        this.setData(JSON.parse(savedMapping));
      })
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  }

  /**
   * When you clear data in select2 choice it puts an empty string instead of null.
   * This function put null instead of empty string in mapping values.
   */
  private cleanMapping(identifiersMapping: { [key: string]: string }): { [key: string]: (string | null) } {
    return Object.keys(identifiersMapping).reduce((accumulator: { [key: string]: (string | null) }, index: string) => {
      accumulator[index] = '' !== identifiersMapping[index] ? identifiersMapping[index] : null;

      return accumulator;
    }, {});
  }
}

export = MappingSave
