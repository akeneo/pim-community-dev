/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as _ from 'underscore';

import {AttributesMapping} from '../../model/attributes-mapping';

import View = require('pimui/js/view/base');

const __ = require('oro/translator');
const template = require('akeneo/franklin-insights/template/settings/attributes-mapping/family-progress');

class FamilyProgress extends View {
  private template = _.template(template);

  public configure(): any {
    super.configure();

    this.getFormModel().on('change', this.render.bind(this));
  }

  public render() {
    const {attributeCount, mappedAttributeCount, suggestedAttributeCount} = this.getFormData() as AttributesMapping;

    const progressBarTitle = __(
      'akeneo_franklin_insights.entity.attributes_mapping.module.index.family_progress.mapped_attribute',
      {
        mapped_count: `${mappedAttributeCount}/${attributeCount}`,
        suggested_count: `${suggestedAttributeCount}/${attributeCount}`
      }
    );

    this.$el.html(this.template({__, attributeCount, mappedAttributeCount, suggestedAttributeCount, progressBarTitle}));

    return super.render();
  }
}

export = FamilyProgress;
