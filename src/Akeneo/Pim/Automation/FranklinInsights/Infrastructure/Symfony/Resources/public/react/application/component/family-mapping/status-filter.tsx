/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useContext} from 'react';

import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {TranslateContext} from '../../context/translate-context';
import {Translate} from '../shared/translate';

interface FilterLabel {
  value: AttributeMappingStatus | null;
  label: string;
}

interface Props {
  onSelect: (status: AttributeMappingStatus | null) => void;
  selectedStatus: AttributeMappingStatus | null;
}

const statuses: FilterLabel[] = [
  {value: null, label: 'pim_common.all'},
  {
    value: AttributeMappingStatus.PENDING,
    label: 'akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_insights.pending'
  },
  {
    value: AttributeMappingStatus.ACTIVE,
    label: 'akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_insights.active'
  },
  {
    value: AttributeMappingStatus.INACTIVE,
    label: 'akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_insights.inactive'
  }
];

export const StatusFilter = ({onSelect, selectedStatus}: Props) => {
  const translate = useContext(TranslateContext);

  return (
    <div className='AknDropdown AknFilterBox-filterContainer'>
      <div className='AknFilterBox-filter' data-toggle='dropdown'>
        <span className='AknFilterBox-filterLabel'>
          <Translate id='pim_common.status' />
        </span>
        <div className='AknFilterBox-filterCriteria'>
          <span className='filter-criteria-hint'>
            {translate((statuses.find(({value}) => value === selectedStatus) as FilterLabel).label)}
          </span>
          <span className='AknFilterBox-filterCaret'></span>
        </div>
      </div>

      <ul className='AknDropdown-menu'>
        <div className='AknDropdown-menuTitle'>
          <Translate id='pim_common.status' />
        </div>

        {statuses.map(({value, label}, i) => (
          <li key={i} onClick={() => onSelect(value)}>
            <a className='AknDropdown-menuLink'>{translate(label)}</a>
          </li>
        ))}
      </ul>
    </div>
  );
};
