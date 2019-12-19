/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from "react";
import {
  DataQualityOverviewChart
} from 'akeneodataqualityinsights-react';
import {
  DataQualityOverviewHeader
} from 'akeneodataqualityinsights-react';
import {
  DataQualityOverviewChartHeader
}
  from 'akeneodataqualityinsights-react';
interface SectionConfig {
  align: string;
}
const UserContext = require('pim/user-context');

/**
 * Section view for Data Quality Insights Dashboard
 *
 * @author    Anaïs Baune Lemaire <anais.lemaire@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SectionView extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
  };

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {

    const catalogLocale: string = UserContext.get('catalogLocale');
    const catalogChannel: string = UserContext.get('catalogScope');

    ReactDOM.render(
      <>
        <div>
          <DataQualityOverviewHeader/>
          <DataQualityOverviewChartHeader/>
          <DataQualityOverviewChart catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
        </div>
      </>,
    this.el
    );
    return this;
  }
}

export = SectionView;
