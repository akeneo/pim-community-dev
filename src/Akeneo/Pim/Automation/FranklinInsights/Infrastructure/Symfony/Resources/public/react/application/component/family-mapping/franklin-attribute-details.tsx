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

interface Props {
  label: string;
  type: string;
  samples: string[];
}

export const FranklinAttributeDetails = ({label, type, samples}: Props) => (
  <>
    <div className='label'>{label}</div>
    <div className='type'>
      <Translate
        id='akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_attribute_type'
        placeholders={{type}}
      />
    </div>
    <div className='summary'>
      <span className='title'>
        <Translate id='akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_value_sample' />
      </span>
      <ol>
        {samples.map((sample, i) => (
          <li key={i}>{sample}</li>
        ))}
      </ol>
    </div>
  </>
);
