/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import SimpleSelectAttribute = require("../common/simple-select-attribute");
import NormalizedAttribute from 'pim/model/attribute';
import * as _ from 'underscore';
const warningTemplate = require('akeneo/suggest-data/template/settings/attributes-mapping/warning');
const __ = require('oro/translator');

interface Config {
  config: {
    perfectMappings: string[];
    fieldName: string;
    label: string;
    choiceRoute: string;
    types: string[];
  };
  className: string
}

/**
 * TODO
 */
class SimpleSelectAttributeWithWarning extends SimpleSelectAttribute {
  private static readonly warningTemplate: ((...data: any[]) => string) = _.template(warningTemplate);
  private readonly perfectMappings: string[];

  /**
   * {@inheritdoc}
   */
  constructor(options: Config) {
    super({
      ...{ className: 'AknFieldContainer AknFieldContainer--withoutMargin' }, ...options,
    });

    this.perfectMappings = options.config.perfectMappings;
  }

  /**
   * {@inheritdoc}
   */
  select2InitSelection(element: any, callback: any): void {
    const code = $(element).val();
    if ('' !== code) {
      $.ajax({
        url: this.choiceUrl,
        data: {options: {identifiers: [code]}},
        type: this.choiceVerb
      }).then((response: NormalizedAttribute[]) => {
        const selected = response.find(e => e.code === code);
        if (undefined !== selected) {
          this.toggleWarning(selected.type);
          callback(this.convertBackendItem(selected));
        }
      });
    }
  }

  /**
   *
   * @param type
   */
  private toggleWarning(type: string) {
    if (!this.perfectMappings.includes(type)) {
      this.$el.find('.AknFieldContainer-footer').append(SimpleSelectAttributeWithWarning.warningTemplate({
        message: __('akeneo_suggest_data.entity.attributes_mapping.module.index.types_mismatch_warning')
      }));
    }
  }
}

export = SimpleSelectAttributeWithWarning;
