import React from 'react';
import styled from 'styled-components';
import {Block, Helper, SectionTitle, IconButton, CloseIcon} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, ColumnIdentifier, generateColumnName, MAX_SOURCE_COUNT_FOR_COLLECTION_TARGETS} from '../../models';
import {SourceDropdown} from './SourceDropdown';

const SourcesContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const BlocksContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-top: 10px;
`;

type SourcesProps = {
  isMultiSource: boolean;
  sources: ColumnIdentifier[];
  columns: Column[];
  validationErrors: ValidationError[];
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
};

const Sources = ({sources, columns, validationErrors, isMultiSource, onSourcesChange}: SourcesProps) => {
  const translate = useTranslate();

  const handleAddSource = (selectedColumn: Column) => {
    onSourcesChange([...sources, selectedColumn.uuid]);
  };

  const handleRemoveSource = (sourceToRemove: ColumnIdentifier) => {
    onSourcesChange(sources.filter(source => source !== sourceToRemove));
  };

  const canAddSource = isMultiSource ? MAX_SOURCE_COUNT_FOR_COLLECTION_TARGETS > sources.length : 0 === sources.length;
  const columnsAvailableToAddSource = columns.filter(({uuid}) => !sources.includes(uuid));

  return (
    <SourcesContainer>
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
      <BlocksContainer>
        {sources.map((uuid, index) => {
          const column = columns.find(column => uuid === column.uuid);

          return (
            <Block
              title={column ? generateColumnName(column.index, column.label) : ''}
              key={`${uuid}${index}`}
              actions={
                <>
                  <IconButton
                    icon={<CloseIcon />}
                    onClick={() => handleRemoveSource(uuid)}
                    title={translate('pim_common.remove')}
                    ghost
                    size="small"
                    level="danger"
                  />
                </>
              }
            />
          );
        })}
        {canAddSource ? (
          <SourceDropdown columns={columnsAvailableToAddSource} onColumnSelected={handleAddSource} />
        ) : (
          <Helper inline level="info">
            {translate(
              'akeneo.tailored_import.data_mapping.sources.add.helper',
              {
                limit: isMultiSource ? MAX_SOURCE_COUNT_FOR_COLLECTION_TARGETS : 1,
              },
              isMultiSource ? MAX_SOURCE_COUNT_FOR_COLLECTION_TARGETS : 1
            )}
          </Helper>
        )}
      </BlocksContainer>
    </SourcesContainer>
  );
};

export type {SourcesProps};
export {Sources};
