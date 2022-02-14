import React from 'react';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, ColumnIdentifier, generateColumnName, MAX_SOURCE_COUNT_BY_DATA_MAPPING} from '../../models';
import {SourceDropdown} from './SourceDropdown';

type SourcesProps = {
  sources: ColumnIdentifier[];
  columns: Column[];
  validationErrors: ValidationError[];
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
};

const Sources = ({sources, columns, validationErrors, onSourcesChange}: SourcesProps) => {
  const translate = useTranslate();

  const handleAddSource = (selectedColumn: Column) => {
    onSourcesChange([...sources, selectedColumn.uuid]);
  };

  const canAddSource = MAX_SOURCE_COUNT_BY_DATA_MAPPING > sources.length;

  return (
    <>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.sources.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {validationErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      <ul>
        {sources.map((uuid, index) => {
          const column = columns.find(column => uuid === column.uuid);

          return <li key={`${uuid}${index}`}>{column ? generateColumnName(column) : ''}</li>;
        })}
      </ul>
      <SourceDropdown columns={columns} onColumnSelected={handleAddSource} disabled={!canAddSource} />
    </>
  );
};

export {Sources};
