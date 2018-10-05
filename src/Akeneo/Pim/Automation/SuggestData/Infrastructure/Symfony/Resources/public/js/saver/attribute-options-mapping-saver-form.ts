import AttributeOptionsMappingSaver = require("./attribute-options-mapping-saver");
const BaseSaverForm = require('pim/form/common/save-form');

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class SaverForm extends BaseSaverForm {
  /**
   * {@inheritdoc}
   */
  public save(): JQueryPromise<any> {
    const entity = this.getFormData();

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return AttributeOptionsMappingSaver
      .setFamilyCode(this.getFormData().family)
      .setPimAiAttributeCode(this.getFormData().pim_ai_attribute)
      .setUrl(this.config.url)
      .save(entity.code, entity, this.config.method || 'POST')
      .then((data: any) => {
        this.postSave();
        this.setData(data);
        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
      })
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  }
}

export = SaverForm;
