/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useSelector} from 'react-redux';

import {FamilyMappingState} from '../../reducer/family-mapping';
import {FilterableGrid} from './filterable-grid';

export const FamilyMapping = () => {
  const mapping = useSelector((state: FamilyMappingState) => state.familyMapping.mapping);

  if (!mapping) {
    return <>No mapping !</>;
  }

  return <FilterableGrid mapping={mapping} />;
};
