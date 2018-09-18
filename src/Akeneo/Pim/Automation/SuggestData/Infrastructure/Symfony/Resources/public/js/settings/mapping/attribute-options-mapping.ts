import BaseForm = require('pimenrich/js/view/base');
import * as _ from "underscore";

const __ = require('oro/translator');
const template = require('pimee/template/settings/mapping/attribute-options-mapping');

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMapping extends BaseForm {
  readonly template: any = _.template(template);
  private familyLabel: string;
  private pimAiAttributeLabel: string;

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html(this.template({
      title: __('akeneo_suggest_data.entity.attribute_options_mapping.module.edit.title', {
        familyLabel: this.familyLabel,
        pimAiAttributeLabel: this.pimAiAttributeLabel
      })
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
}

export = AttributeOptionsMapping
