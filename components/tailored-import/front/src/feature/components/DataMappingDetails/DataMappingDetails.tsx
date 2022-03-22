import React from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping, Target, ColumnIdentifier} from '../../models';
import {Sources} from './Sources';
import {TargetParameters} from './TargetParameters';

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
  validationErrors: ValidationError[];
  onDataMappingChange: (dataMapping: DataMapping) => void;
};

const DataMappingDetails = ({columns, dataMapping, validationErrors, onDataMappingChange}: ColumnDetailsProps) => {
  const translate = useTranslate();

  const handleSourcesChange = (sources: ColumnIdentifier[]) => {
    onDataMappingChange({...dataMapping, sources});
  };

  const handleTargetParametersChange = (target: Target) => {
    onDataMappingChange({...dataMapping, target});
  };

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
      </SectionTitle>
      <TargetParameters
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={handleTargetParametersChange}
      />
      <Sources
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={handleSourcesChange}
      />
    </Container>
  );
};

export {DataMappingDetails};
