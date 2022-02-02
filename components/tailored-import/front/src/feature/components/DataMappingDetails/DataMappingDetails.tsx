import React from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {
  Column,
  DataMapping,
  generateColumnName,
  MAX_SOURCE_COUNT_BY_DATA_MAPPING,
  isAttributeTarget,
  Target,
} from '../../models';
import {SourceDropdown} from '../SourceDropdown';
import {AttributeTargetParameters} from './AttributeTargetParameters';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
`;

type ColumnDetailsProps = {
  columns: Column[];
  dataMapping: DataMapping;
  onDataMappingChange: (dataMapping: DataMapping) => void;
};

const DataMappingDetails = ({columns, dataMapping, onDataMappingChange}: ColumnDetailsProps) => {
  const translate = useTranslate();
  const canAddSource = MAX_SOURCE_COUNT_BY_DATA_MAPPING > dataMapping.sources.length;

  const handleAddSource = (selectedColumn: Column) => {
    onDataMappingChange({...dataMapping, sources: [...dataMapping.sources, selectedColumn.uuid]});
  };

  const handleTargetParametersChange = (target: Target) => {
    onDataMappingChange({...dataMapping, target});
  };

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
      </SectionTitle>
      {isAttributeTarget(dataMapping.target) && (
        <AttributeTargetParameters target={dataMapping.target} onTargetChange={handleTargetParametersChange} />
      )}
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.sources')}</SectionTitle.Title>
      </SectionTitle>
      <ul>
        {dataMapping.sources.map((uuid, index) => {
          const column = columns.find(column => uuid === column.uuid);

          return <li key={`${uuid}${index}`}>{column ? generateColumnName(column) : ''}</li>;
        })}
      </ul>
      <SourceDropdown columns={columns} onColumnSelected={handleAddSource} disabled={!canAddSource} />
    </Container>
  );
};

export {DataMappingDetails};
