import React, {useState} from 'react';
import styled from 'styled-components';
import {IconButton, placeholderStyle, RefreshIcon, SectionTitle, Preview, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DataMapping} from '../../models';

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

  ${({isLoading}) => isLoading && placeholderStyle}
`;

type OperationsProps = {
  dataMapping: DataMapping;
  onRefreshSampleData: (index: number) => Promise<void>;
};

const Operations = ({dataMapping, onRefreshSampleData}: OperationsProps) => {
  const translate = useTranslate();
  const [loadingSampleData, setLoadingSampleData] = useState<number[]>([]);

  const handleRefreshSampleData = async (indexToRefresh: number) => {
    setLoadingSampleData(loadingSampleData => [...loadingSampleData, indexToRefresh]);
    await onRefreshSampleData(indexToRefresh);
    setLoadingSampleData(loadingSampleData => loadingSampleData.filter(value => indexToRefresh !== value));
  };

  return (
    <OperationsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.operations.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {0 < dataMapping.sample_data.length && (
        <Preview title={translate('akeneo.tailored_import.data_mapping.preview.title')}>
          {dataMapping.sample_data.map((sampleData, key) => (
            <Preview.Row
              key={key}
              action={
                <IconButton
                  disabled={loadingSampleData.includes(key)}
                  icon={<RefreshIcon />}
                  onClick={() => handleRefreshSampleData(key)}
                  title={translate('akeneo.tailored_import.data_mapping.preview.refresh')}
                />
              }
            >
              {null !== sampleData ? (
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
export type {OperationsProps};
