import BaseForm = require('pimenrich/js/view/base');
import * as _ from "underscore";

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const template = require('pimee/template/settings/mapping/attribute-options-mapping');

interface NormalizedAttributeOptionsMapping {

}

interface Config {
  labels: {
    pending: string,
    mapped: string,
    unmapped: string,
    pim_ai_attribute_option: string,
    catalog_attribute_option: string,
    suggest_data: string
  }
}

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMapping extends BaseForm {
  private static readonly ATTRIBUTE_OPTION_PENDING: number = 0;
  private static readonly ATTRIBUTE_OPTION_MAPPED: number = 1;
  private static readonly ATTRIBUTE_OPTION_UNMAPPED: number = 2;
  readonly template: any = _.template(template);
  private familyLabel: string;
  private familyCode: string;
  private pimAttributeCode: string;
  private pimAiAttributeLabel: string;
  readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pim_ai_attribute_option: '',
      catalog_attribute_option: '',
      suggest_data: '', // TODO Rename to attribute_option_code_mapping
    }
  };
  private mapping: NormalizedAttributeOptionsMapping | null = null;

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    if (this.mapping === null) {
      this.mapping = this.fetchMapping();
    }
    this.$el.html(this.template({
      title: __('akeneo_suggest_data.entity.attribute_options_mapping.module.edit.title', {
        familyLabel: this.familyLabel,
        pimAiAttributeLabel: this.pimAiAttributeLabel,
      }),
      mapping: {},
      pim_ai_attribute_option: __(this.config.labels.pim_ai_attribute_option),
      catalog_attribute_option: __(this.config.labels.catalog_attribute_option),
      suggest_data: __(this.config.labels.suggest_data),
      statuses: this.getMappingStatuses(),
    }));

    this.renderExtensions();

    return this;
  }

  public setFamilyLabel(familyLabel: string): AttributeOptionsMapping {
    this.familyLabel = familyLabel;

    return this;
  }

  public setPimAiAttributeLabel(pimAiAttributeLabel: string): AttributeOptionsMapping {
    this.pimAiAttributeLabel = pimAiAttributeLabel;

    return this;
  }

  public setFamilyCode(familyCode: string): AttributeOptionsMapping {
    this.familyCode = familyCode;

    return this;
  }

  public setPimAttributeCode(pimAttributeCode: string): AttributeOptionsMapping {
    this.pimAttributeCode = pimAttributeCode;

    return this;
  }

  /**
   * @returns {{ [ key: number ]: string }}
   */
  private getMappingStatuses() {
    const statuses: { [key: number]: string } = {};
    statuses[AttributeOptionsMapping.ATTRIBUTE_OPTION_PENDING] = __(this.config.labels.pending);
    statuses[AttributeOptionsMapping.ATTRIBUTE_OPTION_MAPPED] = __(this.config.labels.mapped);
    statuses[AttributeOptionsMapping.ATTRIBUTE_OPTION_UNMAPPED] = __(this.config.labels.unmapped);

    return statuses;
  }

  private fetchMapping(): NormalizedAttributeOptionsMapping {
    FetcherRegistry
      .getFetcher('attribute-options-mapping')
      .fetch(this.familyCode, {attributeCode: this.pimAttributeCode})
      .then((toto: any) => {
        console.log(toto);
      });

    return {};
  }
}

export = AttributeOptionsMapping
