import React, {useState} from 'react';
import {Helper, RulesIllustration, Search, SectionTitle, Table, uuid} from 'akeneo-design-system';
import styled from 'styled-components';
import {NoDataSection, NoDataTitle, useTranslate} from '@akeneo-pim-community/shared';
import {DataMappingRow} from './DataMappingRow';
import {useValidationErrors} from '../../contexts';
import {Requirement, searchRequirements, DataMapping, createDataMapping} from '../../models';

const Container = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;

const SourceDataHeaderCell = styled(Table.HeaderCell)`
  padding-left: 20px;
`;

const DataMappingNameHeaderCell = styled(Table.HeaderCell)`
  width: 300px;
`;

const SpacedSearch = styled(Search)`
  margin: 20px 0;
`;

type DataMappingListProps = {
  dataMappings: DataMapping[];
  requirements: Requirement[];
  selectedRequirement: string | null;
  onDataMappingSelected: (dataMappingUuid: string | null) => void;
};

const DataMappingList = ({
  selectedRequirement,
  dataMappings,
  requirements,
  onDataMappingSelected,
}: DataMappingListProps) => {
  const selectedDataMapping: DataMapping | null =
    dataMappings.find(({target}) => selectedRequirement === target.name) ?? null;
  const translate = useTranslate();
  const [searchValue, setSearchValue] = useState<string>('');

  const globalErrors = useValidationErrors('[data_mappings]', true);
  const filteredRequirements = searchRequirements(requirements, searchValue);

  const shouldDisplayNoResults = 0 === filteredRequirements.length && '' !== searchValue;
  const shouldDisplayTable = !shouldDisplayNoResults;

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.syndication.data_mapping_list.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
      </SectionTitle>
      {
        <SpacedSearch
          sticky={44}
          placeholder={translate('pim_common.search')}
          searchValue={searchValue}
          onSearchChange={setSearchValue}
        >
          <Search.ResultCount>
            {translate(
              'pim_common.result_count',
              {itemsCount: filteredRequirements.length},
              filteredRequirements.length
            )}
          </Search.ResultCount>
        </SpacedSearch>
      }
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {shouldDisplayTable && (
        <Table isDragAndDroppable={false}>
          <Table.Header sticky={88}>
            <Table.HeaderCell></Table.HeaderCell>
            <DataMappingNameHeaderCell>
              {translate('akeneo.syndication.data_mapping_list.header.data_mapping_name')}
            </DataMappingNameHeaderCell>
            <Table.HeaderCell />
            <SourceDataHeaderCell>
              {translate('akeneo.syndication.data_mapping_list.header.source_data')}
            </SourceDataHeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {filteredRequirements.map(requirement => {
              const dataMapping =
                dataMappings.find(({target}) => target.name === requirement.code) ??
                createDataMapping(requirement, uuid());
              // We should handle the case where the data mapping cretation fail because this can happend if code is not ready for a new target type

              return (
                <DataMappingRow
                  key={dataMapping.uuid}
                  dataMapping={dataMapping}
                  isSelected={selectedDataMapping?.uuid === dataMapping.uuid}
                  onDataMappingSelected={() => onDataMappingSelected(requirement.code)}
                />
              );
            })}
          </Table.Body>
        </Table>
      )}
      {shouldDisplayNoResults && (
        <NoDataSection>
          <RulesIllustration size={256} />
          <NoDataTitle>{translate('pim_common.no_search_result')}</NoDataTitle>
        </NoDataSection>
      )}
    </Container>
  );
};

export {DataMappingList};
