import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');

const simpleSelectAttribute = require('akeneosuggestdata/js/settings/mapping/simple-select-attribute');
const fetcherRegistry = require('pim/fetcher-registry');
const __ = require('oro/translator');
const template = require('pimee/template/settings/mapping/identifiers');

/**
 * Maps pim.ai identifiers with akeneo attributes.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class EditIdentifiersMappingView extends BaseView {
  readonly template = _.template(template);

  readonly headers = {
    'identifiersLabel': __('akeneo_suggest_data.entity.identifier_mapping.fields.identifier_label.label'),
    'attributeLabel': __('akeneo_suggest_data.entity.identifier_mapping.fields.catalog_attribute'),
    'suggestDataLabel': __('akeneo_suggest_data.entity.identifier_mapping.fields.suggest_data'),
  };

  private identifiersStatuses: {[key: string]: string} = {};

  readonly config: Object = {};

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Object }) {
    super({...options, ...{
        className: 'AknGrid AknGrid--unclickable',
        tagName: 'table'
      }});

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      fetcherRegistry.getFetcher('identifiers-mapping').fetchAll().then((identifiersMapping: any) => {
        this.setData(identifiersMapping);
        this.updateIdentifierStatuses();

        this.listenTo(
          this.getRoot(),
          'pim_enrich:form:entity:post_save',
          this.triggerUpdateIdentifierStatuses.bind(this)
        );
      })
    );
  };

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const identifiersMapping: {[key: string]: string} = this.getFormData();

    this.$el.html(this.template({
      headers: this.headers,
      identifiers: identifiersMapping,
      identifiersStatuses: this.identifiersStatuses,
      __
    }));

    this.renderAttributeSelectors(identifiersMapping);

    return this;
  }

  /**
   * Renders a simple select attribute field for each PIM.ai identifiers.
   *
   * @param identifiersMapping
   */
  private renderAttributeSelectors(identifiersMapping: {[key: string]: string}): void {
    Object.keys(identifiersMapping).forEach((pimAiAttributeCode: string) => {
      const attributeSelector = new simpleSelectAttribute({
        config: {
          fieldName: pimAiAttributeCode,
          label: '',
          choiceRoute: 'pim_enrich_attribute_rest_index'
        }
      });
      attributeSelector.setParent(this);

      const $dom = this.$el.find('.attribute-selector[data-identifier="' + pimAiAttributeCode + '"]');
      $dom.html(attributeSelector.render().$el);
    });
  }

  /**
   * Updates the mapping status of each identifiers after a successful save.
   */
  private triggerUpdateIdentifierStatuses(): void {
    this.updateIdentifierStatuses();
    this.render();
  }

  /**
   * Updates the mapping status of each identifiers: active or inactive.
   */
  private updateIdentifierStatuses(): void {
    const identifiersMapping: {[key: string]: string} = this.getFormData();

    Object.keys(identifiersMapping).forEach((pimAiAttributeCode: string) => {
      null === identifiersMapping[pimAiAttributeCode] || '' === identifiersMapping[pimAiAttributeCode]
        ? this.identifiersStatuses[pimAiAttributeCode] = 'inactive'
        : this.identifiersStatuses[pimAiAttributeCode] = 'active';
    });
  }
}

export = EditIdentifiersMappingView;
