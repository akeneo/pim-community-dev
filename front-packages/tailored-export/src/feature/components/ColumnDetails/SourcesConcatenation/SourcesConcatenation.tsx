import React from 'react';
import styled from 'styled-components';
import {Button, Checkbox, SectionTitle, uuid} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnPreview} from './ColumnPreview';
import {ColumnConfiguration} from '../../../models';
import {ConcatenationList} from './ConcatenationList';

const SourcesConcatenationContainer = styled.div`
  height: 400px;
  display: flex;
  flex-direction: column;
  gap: 10px;
`;

type SourcesConcatenationProps = {
  columnConfiguration: ColumnConfiguration;
  onColumnConfigurationChange: (columnConfiguration: ColumnConfiguration) => void;
};

const SourcesConcatenation = ({columnConfiguration, onColumnConfigurationChange}: SourcesConcatenationProps) => {
  const translate = useTranslate();

  const handleSpacesBetweenChange = (spaceBetween: boolean) => {
    onColumnConfigurationChange({...columnConfiguration, format: {...columnConfiguration.format, spaceBetween}});
  };

  const handleAddText = () => {
    onColumnConfigurationChange({
      ...columnConfiguration,
      format: {
        ...columnConfiguration.format,
        elements: [...columnConfiguration.format.elements, {uuid: uuid(), type: 'string', value: ''}],
      },
    });
  };

  return (
    <SourcesConcatenationContainer>
      <SectionTitle>
        <SectionTitle.Title>
          {translate('akeneo.tailored_export.column_details.concatenation.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <ColumnPreview columnConfiguration={columnConfiguration} />
      <Checkbox checked={columnConfiguration.format.spaceBetween} onChange={handleSpacesBetweenChange}>
        {translate('akeneo.tailored_export.column_details.concatenation.space_between')}
      </Checkbox>
      <ConcatenationList
        columnConfiguration={columnConfiguration}
        onColumnConfigurationChange={onColumnConfigurationChange}
      />
      <div>
        <Button level="secondary" ghost={true} onClick={handleAddText}>
          {translate('akeneo.tailored_export.column_details.concatenation.add_text')}
        </Button>
      </div>
    </SourcesConcatenationContainer>
  );
};

export {SourcesConcatenation};
