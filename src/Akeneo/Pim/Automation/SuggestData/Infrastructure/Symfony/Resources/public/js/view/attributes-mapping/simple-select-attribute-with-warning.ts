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
  private readonly perfectMappings: string[];
  // private cacheMapping: { [attributeCode: string]: string } = {};

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
      this.$el.find('.AknFieldContainer-footer').append('<span class="AknFieldContainer-validationError">' +
        '            <i class="icon-warning-sign"></i>' +
        '            <span class="error-message">message</span>' +
        '        </span>');
    }
  }
}

export = SimpleSelectAttributeWithWarning;
