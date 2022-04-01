import React from 'react';
import {DataMapping} from '../../models';
import styled from 'styled-components';
import {IconButton, RefreshIcon, SectionTitle, Preview, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {placeholderStyle} from 'akeneo-design-system';

type OperationsProps = {
  dataMapping: DataMapping;
  onRefreshSampleData: (index: number) => void;
  loadingSampleData: number[];
};

const OperationsContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 10px;
`;

const PreviewContent = styled.div<{isLoading: boolean}>`
  overflow: hidden;
  max-width: 100ch;
  text-overflow: ellipsis;
  white-space: nowrap;

  ${({isLoading}) => isLoading && placeholderStyle}
`;

const EmptyPreviewContent = styled(PreviewContent)`
  color: ${getColor('grey', 100)};
`;

const Operations = ({dataMapping, loadingSampleData, onRefreshSampleData}: OperationsProps) => {
  const translate = useTranslate();

  return (
    <OperationsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.operations.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {dataMapping.sample_data.length > 0 && (
        <Preview title={translate('akeneo.tailored_import.data_mapping.preview.title')}>
          {dataMapping.sample_data.map((sampleData, key) => (
            <Preview.Row
              key={key}
              action={
                <IconButton
                  disabled={loadingSampleData.includes(key)}
                  icon={<RefreshIcon />}
                  onClick={() => onRefreshSampleData(key)}
                  title={translate('akeneo.tailored_import.data_mapping.preview.refresh')}
                />
              }
            >
              {sampleData ? (
                <PreviewContent isLoading={loadingSampleData.includes(key)}>{sampleData}</PreviewContent>
              ) : (
                <EmptyPreviewContent isLoading={loadingSampleData.includes(key)}>
                  {translate('akeneo.tailored_import.data_mapping.preview.placeholder')}
                </EmptyPreviewContent>
              )}
            </Preview.Row>
          ))}
        </Preview>
      )}
    </OperationsContainer>
  );
};

export {Operations};
