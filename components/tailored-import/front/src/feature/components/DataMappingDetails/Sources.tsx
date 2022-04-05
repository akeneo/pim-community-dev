import React from 'react';
import styled from 'styled-components';
import {Block, Helper, SectionTitle, IconButton, CloseIcon} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, ColumnIdentifier, generateColumnName, MAX_SOURCE_COUNT_BY_DATA_MAPPING} from '../../models';
import {SourceDropdown} from './SourceDropdown';

const SourcesContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 10px;
`;

const BlocksContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

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

  const handleRemoveSource = (sourceToRemove: ColumnIdentifier) => {
    onSourcesChange(sources.filter(source => source !== sourceToRemove));
  };

  const canAddSource = MAX_SOURCE_COUNT_BY_DATA_MAPPING > sources.length;
  const columnsAvailableToAddSource = columns.filter(({uuid}) => !sources.includes(uuid));

  return (
    <SourcesContainer>
      <div>
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
      </div>
      {0 < sources.length && (
        <BlocksContainer>
          {sources.map((uuid, index) => {
            const column = columns.find(column => uuid === column.uuid);

            return (
              <Block
                key={`${uuid}${index}`}
                action={
                  <IconButton
                    icon={<CloseIcon />}
                    onClick={() => handleRemoveSource(uuid)}
                    title={translate('pim_common.remove')}
                  />
                }
              >
                {column ? generateColumnName(column.index, column.label) : ''}
              </Block>
            );
          })}
        </BlocksContainer>
      )}
      <>
        {canAddSource ? (
          <SourceDropdown columns={columnsAvailableToAddSource} onColumnSelected={handleAddSource} />
        ) : (
          <Helper inline level="info">
            {translate(
              'akeneo.tailored_import.data_mapping.sources.add.helper',
              {
                limit: MAX_SOURCE_COUNT_BY_DATA_MAPPING,
              },
              MAX_SOURCE_COUNT_BY_DATA_MAPPING
            )}
          </Helper>
        )}
      </>
    </SourcesContainer>
  );
};

export type {SourcesProps};
export {Sources};
