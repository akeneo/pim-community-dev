/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {DataQualityInsightsFeature, getDataQualityInsightsFeature} from '@akeneo-pim-community/data-quality-insights/src/index';

const BaseItem = require('pim/menu/item');

/**
 * @author Anais Baune Lemaire <anais.lemaire@akeneo.com>
 */
class MenuItem extends BaseItem {
  public render(): any {
    return getDataQualityInsightsFeature().then((dataQualityInsightsFeature: DataQualityInsightsFeature) => {
      return dataQualityInsightsFeature.isActive ? BaseItem.prototype.render.apply(this) : null;
    });
  }
}

export = MenuItem;
