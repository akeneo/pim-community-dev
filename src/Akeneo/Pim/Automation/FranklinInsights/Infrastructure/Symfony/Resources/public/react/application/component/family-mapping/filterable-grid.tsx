/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useState, useContext} from 'react';
import {useDispatch, useSelector} from 'react-redux';

import {AttributeMapping} from '../../../domain/model/attribute-mapping';
import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {AttributesMapping} from '../../../domain/model/attributes-mapping';
import {updateCodeOrLabelFilter, updateStatusFilter} from '../../action/family-mapping/search-franklin-attributes';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {selectFilteredFranklinAttributeCodes} from '../../selector/select-visible-franklin-attribute-codes';
import {Grid} from './grid';
import {SearchFilter} from './search-filter';
import {StatusFilter} from './status-filter';
import {TranslateContext} from '../../context/translate-context';
import {NoResultsGrid} from './no-results-grid';
import {Loader} from './loader';

interface Props {
  mapping: AttributesMapping;
}

export const FilterableGrid = ({mapping}: Props) => {
  const translate = useContext(TranslateContext);
  const dispatch = useDispatch();
  const handleOnSearch = (searchTerms: string) => dispatch(updateCodeOrLabelFilter(searchTerms));
  const handleOnSelectStatus = (status: AttributeMappingStatus | null) => dispatch(updateStatusFilter(status));

  const selectedFranklinAttributeCodes: string[] = useSelector(
    (state: FamilyMappingState) => state.selectedFranklinAttributeCodes
  );
  const selectedStatus: AttributeMappingStatus | null = useSelector(
    (state: FamilyMappingState) => state.searchFranklinAttributes.status
  );
  const searchTerms: string | undefined = useSelector((state: FamilyMappingState) => {
    return '' === state.searchFranklinAttributes.codeOrLabel ? undefined : state.searchFranklinAttributes.codeOrLabel;
  });

  const filteredFranklinAttributeCodes: string[] = useSelector(selectFilteredFranklinAttributeCodes);
  const filteredRows: AttributeMapping[] = filterVisibleRows(mapping, filteredFranklinAttributeCodes);

  const [isOrdered, setIsOrdered] = useState(false);
  if (false === isOrdered) {
    filteredRows.sort(sortAttributesMappingByStatus);
    setIsOrdered(true);
  }

  const isLoading = useSelector((state: FamilyMappingState) => {
    return state.familyMapping.loadFamilyMapping || state.attributes.loadAttributes;
  });

  return (
    <>
      <div className='attribute-mapping-search-container'>
        <div className='AknFilterBox AknFilterBox--search attribute-mapping-search' data-drop-zone='filters'>
          <SearchFilter
            onSearch={handleOnSearch}
            placeholder={translate('akeneo_franklin_insights.entity.attributes_mapping.fields.search_bar.placeholder')}
            initialSearchTerms={searchTerms}
          />
          <StatusFilter onSelect={handleOnSelectStatus} selectedStatus={selectedStatus} />
        </div>
      </div>
      {isLoading ? (
        <Loader />
      ) : filteredRows.length === 0 ? (
        <NoResultsGrid />
      ) : (
        <Grid mapping={filteredRows} selectedFranklinAttributeCodes={selectedFranklinAttributeCodes} />
      )}
    </>
  );
};

function filterVisibleRows(mapping: AttributesMapping, filteredFranklinAttributeCodes: string[]): AttributeMapping[] {
  return Object.values(mapping).filter((attributeMapping: AttributeMapping) =>
    filteredFranklinAttributeCodes.includes(attributeMapping.franklinAttribute.code)
  );
}

function sortAttributesMappingByStatus(a: AttributeMapping, b: AttributeMapping): number {
  if (a.status === b.status) {
    return 0;
  }

  if (AttributeMappingStatus.PENDING === a.status || AttributeMappingStatus.INACTIVE === b.status) {
    return -1;
  }

  return 1;
}
