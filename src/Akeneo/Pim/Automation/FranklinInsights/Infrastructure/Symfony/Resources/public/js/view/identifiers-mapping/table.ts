/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';
import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import SimpleSelectAttribute = require('../common/simple-select-attribute');

const FetcherRegistry = require('pim/fetcher-registry');
const __ = require('oro/translator');
const template = require('akeneo/franklin-insights/template/settings/identifiers-mapping/table');

/**
 * Maps Franklin identifiers with akeneo attributes.
 *
 * The attribute types authorized for the identifiers mapping are defined in
 * SaveIdentifiersMappingHandler::ALLOWED_ATTRIBUTE_TYPES_AS_IDENTIFIER
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class EditIdentifiersMappingView extends BaseView {
  private static readonly VALID_MAPPING: string[] = [
    'pim_catalog_identifier',
    'pim_catalog_text',
  ];

  /**
   * Returns the class for a row depending of the identifier mapping status
   *
   * @param {string} status
   *
   * @returns {string}
   */
  private static getRowClass(status: string): string {
    if (status === 'active') {
      return 'AknGrid-bodyRow--success';
    }

    return '';
  }

  public readonly template = _.template(template);
  public readonly config: object = {};
  public readonly headers = {
    identifiersLabel: __('akeneo_franklin_insights.entity.identifier_mapping.fields.identifier_label.label'),
    attributeLabel: __('akeneo_franklin_insights.entity.identifier_mapping.fields.catalog_attribute'),
    franklinInsightsLabel: __('akeneo_franklin_insights.entity.identifier_mapping.fields.franklin_insights'),
  };
  private identifiersStatuses: { [franklinIdentifier: string]: string } = {};
  private scroll: number = 0;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: object }) {
    super({
      ...options, ...{
        className: 'AknGrid AknGrid--unclickable AknFormContainer--withPadding AknGrid--stretched',
        tagName: 'table',
      },
    });

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    this.listenTo(this.getRoot(), 'pim_enrich:form:render:before', this.saveScroll);
    this.listenTo(this.getRoot(), 'pim_enrich:form:render:after', this.setScroll);
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:entity:post_save',
      this.triggerUpdateIdentifierStatuses.bind(this),
    );

    const identifiersMappingFetcher = FetcherRegistry.getFetcher('identifiers-mapping');
    identifiersMappingFetcher.clear();

    return $.when(
      identifiersMappingFetcher
        .fetchAll()
        .then((identifiersMapping: { [franklinIdentifier: string]: (string | null) }) => {
          this.setData(identifiersMapping);
          this.updateIdentifierStatuses();
        }),
    );
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const identifiersMapping: { [franklinIdentifier: string]: string } = this.getFormData();

    this.$el.html(this.template({
      headers: this.headers,
      identifiers: identifiersMapping,
      identifiersStatuses: this.identifiersStatuses,
      getRowClass: EditIdentifiersMappingView.getRowClass,
      __,
    }));

    this.renderAttributeSelectors(identifiersMapping);
    this.setScroll();

    return this;
  }

  /**
   * Renders a simple select attribute field for each Franklin identifiers.
   *
   * @param identifiersMapping
   */
  private renderAttributeSelectors(identifiersMapping: { [franklinIdentifier: string]: string }): void {
    Object.keys(identifiersMapping).forEach((franklinAttributeCode: string) => {
      const attributeSelector = new SimpleSelectAttribute({
        className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
        config: {
          choiceRoute: 'pim_enrich_attribute_rest_index',
          fieldName: franklinAttributeCode,
          label: '',
          types: EditIdentifiersMappingView.VALID_MAPPING,
        },
      });
      attributeSelector.setParent(this);

      const $dom = this.$el.find('.attribute-selector[data-identifier="' + franklinAttributeCode + '"]');
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
    const identifiersMapping: { [franklinIdentifier: string]: string } = this.getFormData();

    Object.keys(identifiersMapping).forEach((franklinAttributeCode: string) => {
      null === identifiersMapping[franklinAttributeCode] || '' === identifiersMapping[franklinAttributeCode]
        ? this.identifiersStatuses[franklinAttributeCode] = 'inactive'
        : this.identifiersStatuses[franklinAttributeCode] = 'active';
    });
  }

  /**
   * Saves the scroll top position before the page re-rendering
   */
  private saveScroll(): void {
    this.scroll = $('.edit-form').scrollTop() as number;
  }

  /**
   * Puts back the scroll top position after the render.
   */
  private setScroll(): void {
    $('.edit-form').scrollTop(this.scroll);
  }
}

export = EditIdentifiersMappingView;
