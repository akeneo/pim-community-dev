import React, {useState} from 'react';
import styled from 'styled-components';
import {
  AttributesIllustration,
  Helper,
  Placeholder,
  Search,
  SectionTitle,
  Table,
  useDebounce,
} from 'akeneo-design-system';
import {getErrorsForPath, filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping, filterOnColumnLabels, MAX_DATA_MAPPING_COUNT} from '../../models';
import {AddDataMappingDropdown} from '../AddDataMappingDropdown';
import {DataMappingRow} from './DataMappingRow';
import {useIdentifierAttribute} from '../../hooks';

const Container = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;

const SpacedSearch = styled(Search)`
  margin: 20px 0;
`;

type DataMappingListProps = {
  dataMappings: DataMapping[];
  columns: Column[];
  selectedDataMappingUuid: string | null;
  validationErrors: ValidationError[];
  onDataMappingAdded: (dataMapping: DataMapping) => void;
  onDataMappingSelected: (dataMappingUuid: string) => void;
  onDataMappingRemoved: (dataMappingUuid: string) => void;
};

const DataMappingList = ({
  dataMappings,
  columns,
  selectedDataMappingUuid,
  validationErrors,
  onDataMappingAdded,
  onDataMappingSelected,
  onDataMappingRemoved,
}: DataMappingListProps) => {
  const translate = useTranslate();
  const canAddDataMapping = MAX_DATA_MAPPING_COUNT > dataMappings.length;
  const globalErrors = getErrorsForPath(validationErrors, '');
  const [, identifierAttribute] = useIdentifierAttribute();
  const [searchValue, setSearchValue] = useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue);

  const filteredDataMappings = filterOnColumnLabels(dataMappings, columns, debouncedSearchValue);
  const shouldDisplayNoResults = 0 === filteredDataMappings.length && '' !== debouncedSearchValue;

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping_list.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <AddDataMappingDropdown canAddDataMapping={canAddDataMapping} onDataMappingAdded={onDataMappingAdded} />
      </SectionTitle>
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      <SpacedSearch
        sticky={44}
        placeholder={translate('pim_common.search')}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      >
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredDataMappings.length}, filteredDataMappings.length)}
        </Search.ResultCount>
      </SpacedSearch>
      {shouldDisplayNoResults ? (
        <Placeholder
          size="large"
          title={translate('pim_common.no_search_result')}
          illustration={<AttributesIllustration />}
        />
      ) : (
        <Table>
          <Table.Header sticky={88}>
            <Table.HeaderCell>{translate('akeneo.tailored_import.data_mapping.target.title')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('akeneo.tailored_import.data_mapping.sources.title')}</Table.HeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {filteredDataMappings.map(dataMapping => (
              <DataMappingRow
                key={dataMapping.uuid}
                dataMapping={dataMapping}
                columns={columns}
                isSelected={selectedDataMappingUuid === dataMapping.uuid}
                isIdentifierDataMapping={identifierAttribute?.code === dataMapping.target.code}
                hasError={filterErrors(validationErrors, `[${dataMapping.uuid}]`).length > 0}
                onSelect={onDataMappingSelected}
                onRemove={onDataMappingRemoved}
              />
            ))}
          </Table.Body>
        </Table>
      )}
    </Container>
  );
};

export type {DataMappingListProps};

export {DataMappingList};
