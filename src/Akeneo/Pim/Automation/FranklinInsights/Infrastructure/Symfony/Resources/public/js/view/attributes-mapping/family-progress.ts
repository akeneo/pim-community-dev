/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import View = require('pimui/js/view/base');
import * as _ from 'underscore';
import AttributesMapping from '../../model/attributes-mapping';
import AttributesMappingForFamily from '../../model/attributes-mapping-for-family';

const __ = require('oro/translator');
const template = require('akeneo/franklin-insights/template/settings/attributes-mapping/family-progress');

class FamilyProgress extends View {
  private template = _.template(template);

  public configure(): any {
    super.configure();

    this.listenTo(this.getRoot(), this.postUpdateEventName, () => this.render());
  }

  public render() {
    const [attributeCount, mappedAttributeCount] = this.getMappingProgress(
      (this.getFormData() as AttributesMappingForFamily).mapping
    );

    this.$el.html(this.template({__, attributeCount, mappedAttributeCount}));

    return super.render();
  }

  private getMappingProgress = (mappings: AttributesMapping): [number, number] => [
    Object.keys(mappings).length,
    Object.values(mappings).filter(mapping => null !== mapping.attribute && '' !== mapping.attribute).length,
  ];
}

export = FamilyProgress;
