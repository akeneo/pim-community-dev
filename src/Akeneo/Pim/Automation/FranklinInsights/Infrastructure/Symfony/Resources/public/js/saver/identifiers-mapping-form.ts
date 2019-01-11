import {getConnectionStatus} from '../fetcher/franklin-connection';
import ConnectionStatus from '../model/connection-status';

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const BaseSave = require('pim/form/common/save');
const MappingSaver = require('akeneo/franklin-insights/saver/identifiers-mapping');
const Dialog = require('pim/dialog');

interface Mapping {
  [franklinAttribute: string]: (string | null);
}

/**
 * Save extension identifiers mapping
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MappingSave extends BaseSave {
  public readonly updateFailureMessage: string = __('akeneo_franklin_insights.entity.identifier_mapping.flash.update.fail');
  protected updateSuccessMessage: string = __('akeneo_franklin_insights.entity.identifier_mapping.flash.update.success');
  protected isFlash: boolean = true;

  /**
   * {@inheritdoc}
   *
   * Check if there is already an existing mapping. The success message will be set accordingly.
   */
  public configure() {
    return $.when(
      FetcherRegistry.getFetcher('identifiers-mapping').fetchAll().then(
        (identifiersMapping: Mapping) => {
          if (this.isMappingEmpty(identifiersMapping)) {
            this.updateSuccessMessage = __('akeneo_franklin_insights.entity.identifier_mapping.flash.update.first');
            this.isFlash = false;
          }
        },
      ),
      BaseSave.prototype.configure.apply(this, arguments),
    );
  }

  /**
   * {@inheritdoc}
   *
   * Displays a confirmation modal if there is subscribed products. If not, just save.
   */
  public save(): void {
    getConnectionStatus(false).then((connectionStatus: ConnectionStatus) => {
      if (connectionStatus.productSubscriptionCount > 0) {
        Dialog.confirm(
          __(
            'akeneo_franklin_insights.settings.module.save.warning',
            { count: connectionStatus.productSubscriptionCount },
            connectionStatus.productSubscriptionCount,
          ),
          __('akeneo_franklin_insights.settings.module.save.title'),
          this.executeSave,
          null,
          null,
          null,
          'robot',
        );
      } else {
        this.executeSave();
      }
    });
  }

  /**
   * Execute for real the query to save mapping.
   */
  private executeSave(): void {
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
        this.updateSuccessMessage = __('akeneo_franklin_insights.entity.identifier_mapping.flash.update.success');
        this.isFlash = true;
      })
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  }

  /**
   * Checks if at least one Franklin identifier is mapped to an Akeneo attribute.
   *
   * @param {Mapping} identifiersMapping
   *
   * @return {boolean}
   */
  private isMappingEmpty(identifiersMapping: Mapping): boolean {
    const mappedAttributes = Object.keys(identifiersMapping).filter(
      (franklinIdentifier: string) => null !== identifiersMapping[franklinIdentifier],
    );

    return $.isEmptyObject(mappedAttributes);
  }

  /**
   * When you clear data in select2 choice it puts an empty string instead of null.
   * This function put null instead of empty string in mapping values.
   *
   * @param {object} identifiersMapping
   *
   * @return {Mapping}
   */
  private cleanMapping(identifiersMapping: { [franklinAttribute: string]: string }): Mapping {
    return Object.keys(identifiersMapping).reduce((accumulator: Mapping, index: string) => {
      accumulator[index] = '' !== identifiersMapping[index] ? identifiersMapping[index] : null;

      return accumulator;
    }, {});
  }
}

export = MappingSave;
