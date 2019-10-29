/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {Translate} from '../shared/translate';

export const NoResultsGrid = () => {
  return (
    <div className='AknGridContainer-noData no-data-inner'>
      <div className='AknGridContainer-noDataImage '></div>
      <div className='AknGridContainer-noDataTitle'>
        <Translate id='akeneo_franklin_insights.entity.attributes_mapping.search.no_result_title' />
      </div>
      <div className='AknGridContainer-noDataSubtitle'>
        <Translate id='akeneo_franklin_insights.entity.attributes_mapping.search.no_result_subtitle' />
      </div>
    </div>
  );
};
