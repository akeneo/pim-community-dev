import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');
import SimpleSelectAttribute = require('akeneosuggestdata/js/settings/mapping/simple-select-attribute');

const fetcherRegistry = require('pim/fetcher-registry');
const __ = require('oro/translator');
const template = require('pimee/template/settings/mapping/identifiers');

/**
 * Maps pim.ai identifiers with akeneo attributes.
 *
 * The attribute types authorized for the identifiers mapping are defined in
 * Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler::ALLOWED_ATTRIBUTE_TYPES_AS_IDENTIFIER
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class EditIdentifiersMappingView extends BaseView {
  private static readonly VALID_MAPPING: string[] = [
    'pim_catalog_identifier',
    'pim_catalog_number',
    'pim_catalog_simpleselect',
    'pim_catalog_text',
  ];

  private identifiersStatuses: { [key: string]: string } = {};

  readonly template = _.template(template);
  readonly config: Object = {};
  readonly headers = {
    'identifiersLabel': __('akeneo_suggest_data.entity.identifier_mapping.fields.identifier_label.label'),
    'attributeLabel': __('akeneo_suggest_data.entity.identifier_mapping.fields.catalog_attribute'),
    'suggestDataLabel': __('akeneo_suggest_data.entity.identifier_mapping.fields.suggest_data'),
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Object }) {
    super({
      ...options, ...{
        className: 'AknGrid AknGrid--unclickable',
        tagName: 'table'
      }
    });

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  configure(): JQueryPromise<any> {
    return $.when(
      fetcherRegistry.getFetcher('identifiers-mapping')
        .fetchAll()
        .then((identifiersMapping: { [key: string]: (string | null) }) => {
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
    const identifiersMapping: { [key: string]: string } = this.getFormData();

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
  private renderAttributeSelectors(identifiersMapping: { [key: string]: string }): void {
    Object.keys(identifiersMapping).forEach((pimAiAttributeCode: string) => {
      const attributeSelector = new SimpleSelectAttribute({
        config: {
          fieldName: pimAiAttributeCode,
          label: '',
          choiceRoute: 'pim_enrich_attribute_rest_index',
          types: EditIdentifiersMappingView.VALID_MAPPING,
        },
        className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline'
      });
      attributeSelector.setParent(this);

      const $dom = this.$el.find('.attribute-selector[data-identifier="' + pimAiAttributeCode + '"]');
      attributeSelector.configure().then(() => {
        $dom.html(attributeSelector.render().$el);
      });
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
    const identifiersMapping: { [key: string]: string } = this.getFormData();

    Object.keys(identifiersMapping).forEach((pimAiAttributeCode: string) => {
      null === identifiersMapping[pimAiAttributeCode] || '' === identifiersMapping[pimAiAttributeCode]
        ? this.identifiersStatuses[pimAiAttributeCode] = 'inactive'
        : this.identifiersStatuses[pimAiAttributeCode] = 'active';
    });
  }
}

export = EditIdentifiersMappingView;
