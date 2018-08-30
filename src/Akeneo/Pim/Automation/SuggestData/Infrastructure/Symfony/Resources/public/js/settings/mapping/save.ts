const __ = require('oro/translator');
const fetcherRegistry = require('pim/fetcher-registry');
const BaseSave = require('pim/form/common/save');
const MappingSaver = require('pimee/saver/identifier-mapping');

interface MappingInterface {
  [key: string]: (string | null);
}

/**
 * Save extension identifiers mapping
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MappingSave extends BaseSave {
  readonly updateFailureMessage: string = __('akeneo_suggest_data.entity.identifier_mapping.flash.update.fail');
  protected updateSuccessMessage: string = __('akeneo_suggest_data.entity.identifier_mapping.flash.update.success');
  protected isFlash: boolean = true;

  /**
   * {@inheritdoc}
   *
   * Check if there is already an existing mapping. The success message will be set accordingly.
   */
  public configure() {
    return $.when(
      fetcherRegistry.getFetcher('identifiers-mapping').fetchAll().then(
        (identifiersMapping: MappingInterface) => {
          if (this.isMappingEmpty(identifiersMapping)) {
            this.updateSuccessMessage = __('akeneo_suggest_data.entity.identifier_mapping.flash.update.first');
            this.isFlash = false;
          }
        }
      ),
      BaseSave.prototype.configure.apply(this, arguments)
    );
  }

  /**
   * {@inheritdoc}
   */
  public save(): void {
    let identifiersMapping = $.extend(true, {}, this.getFormData());
    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');
    identifiersMapping = this.cleanMapping(identifiersMapping);

    return MappingSaver
      .save(null, identifiersMapping, 'POST')
      .then((savedMapping: string) => {
        this.postSave();
        this.setData(JSON.parse(savedMapping));
        this.getRoot().trigger('pim_enrich:form:entity:post_fetch');
        this.updateSuccessMessage = __('akeneo_suggest_data.entity.identifier_mapping.flash.update.success');
        this.isFlash = true;
      })
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  }

  /**
   * Checks if at least one Franklin identifier is mapped to an Akeneo attribute.
   *
   * @param {MappingInterface} identifiersMapping
   *
   * @return {boolean}
   */
  private isMappingEmpty(identifiersMapping: MappingInterface): boolean {
    const mappedAttributes = Object.keys(identifiersMapping).filter(
      (franklinIdentifier: string) => null !== identifiersMapping[franklinIdentifier]
    );

    return $.isEmptyObject(mappedAttributes);
  }

  /**
   * When you clear data in select2 choice it puts an empty string instead of null.
   * This function put null instead of empty string in mapping values.
   *
   * @param {Object} identifiersMapping
   *
   * @return {MappingInterface}
   */
  private cleanMapping(identifiersMapping: { [key: string]: string }): MappingInterface {
    return Object.keys(identifiersMapping).reduce((accumulator: MappingInterface, index: string) => {
      accumulator[index] = '' !== identifiersMapping[index] ? identifiersMapping[index] : null;

      return accumulator;
    }, {});
  }
}

export = MappingSave
