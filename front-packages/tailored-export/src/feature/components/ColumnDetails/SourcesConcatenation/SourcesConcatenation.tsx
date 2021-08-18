import React from 'react';
import styled from 'styled-components';
import {Button, Checkbox, getColor, Helper, SectionTitle, uuid} from 'akeneo-design-system';
import {filterErrors, getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {ColumnPreview} from './Preview/ColumnPreview';
import {ColumnConfiguration, ConcatElement} from '../../../models';
import {ConcatElementList} from './List/ConcatElementList';

const MAX_TEXT_COUNT = 10;

const SourcesConcatenationContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 10px;
`;

const ConcatenationFooter = styled.div`
  position: sticky;
  bottom: 0;
  background: ${getColor('white')};
  padding-top: 10px;
  display: flex;
  justify-content: flex-end;
`;

type SourcesConcatenationProps = {
  validationErrors: ValidationError[];
  columnConfiguration: ColumnConfiguration;
  onColumnConfigurationChange: (columnConfiguration: ColumnConfiguration) => void;
};

const SourcesConcatenation = ({
  validationErrors,
  columnConfiguration,
  onColumnConfigurationChange,
}: SourcesConcatenationProps) => {
  const translate = useTranslate();
  const globalValidationErrors = getErrorsForPath(validationErrors, '[elements]');

  const handleSpacesBetweenChange = (spaceBetween: boolean) =>
    onColumnConfigurationChange({
      ...columnConfiguration,
      format: {...columnConfiguration.format, space_between: spaceBetween},
    });

  const handleAddText = () =>
    onColumnConfigurationChange({
      ...columnConfiguration,
      format: {
        ...columnConfiguration.format,
        elements: [...columnConfiguration.format.elements, {uuid: uuid(), type: 'string', value: ''}],
      },
    });

  const handleConcatElementChange = (updatedConcatElement: ConcatElement) => {
    const updatedElements = columnConfiguration.format.elements.map(element =>
      element.uuid === updatedConcatElement.uuid ? updatedConcatElement : element
    );

    onColumnConfigurationChange({
      ...columnConfiguration,
      format: {
        ...columnConfiguration.format,
        elements: updatedElements,
      },
    });
  };

  const handleConcatElementRemove = (elementUuid: string) =>
    onColumnConfigurationChange({
      ...columnConfiguration,
      format: {
        ...columnConfiguration.format,
        elements: columnConfiguration.format.elements.filter(({uuid}) => elementUuid !== uuid),
      },
    });

  const handleConcatElementReorder = (newIndices: number[]) =>
    onColumnConfigurationChange({
      ...columnConfiguration,
      format: {
        ...columnConfiguration.format,
        elements: newIndices.map(index => columnConfiguration.format.elements[index]),
      },
    });

  const canAddText = columnConfiguration.format.elements.filter(({type}) => 'string' === type).length < MAX_TEXT_COUNT;

  return (
    <SourcesConcatenationContainer>
      <div>
        <SectionTitle sticky={0}>
          <SectionTitle.Title>
            {translate('akeneo.tailored_export.column_details.concatenation.title')}
          </SectionTitle.Title>
        </SectionTitle>
        {globalValidationErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </div>
      <ColumnPreview columnConfiguration={columnConfiguration} />
      <Checkbox checked={columnConfiguration.format.space_between ?? false} onChange={handleSpacesBetweenChange}>
        {translate('akeneo.tailored_export.column_details.concatenation.space_between')}
      </Checkbox>
      <ConcatElementList
        columnConfiguration={columnConfiguration}
        onConcatElementReorder={handleConcatElementReorder}
        onConcatElementChange={handleConcatElementChange}
        onConcatElementRemove={handleConcatElementRemove}
        validationErrors={filterErrors(validationErrors, '[elements]')}
      />
      <ConcatenationFooter>
        <Button
          title={
            !canAddText
              ? translate('akeneo.tailored_export.validation.concatenation.max_text_count_reached')
              : undefined
          }
          disabled={!canAddText}
          level="secondary"
          ghost={true}
          onClick={handleAddText}
        >
          {translate('akeneo.tailored_export.column_details.concatenation.add_text')}
        </Button>
      </ConcatenationFooter>
    </SourcesConcatenationContainer>
  );
};

export {SourcesConcatenation};
