import * as _ from "underscore";
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
    'identifiersLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.identifiers_label'),
    'attributeLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.attribute_label'),
    'suggestDataLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.suggest_data_label'),
  };

  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      fetcherRegistry.getFetcher('identifiers-mapping').fetchAll().then((identifiersMapping: any) => {
        this.setData(identifiersMapping);
      })
    );
  };

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const identifiersMapping = this.getFormData();
    this.$el.html(this.template({
      headers: this.headers,
      identifiers: identifiersMapping,
      __
    }));

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

    return this;
  }
}

export = EditIdentifiersMappingView;
