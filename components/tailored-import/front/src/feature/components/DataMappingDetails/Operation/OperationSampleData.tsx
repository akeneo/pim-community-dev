import React from 'react';
import {IconButton, Preview, RefreshIcon} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {formatSampleData, SampleData} from '../../../models';
import {PreviewRowData} from './PreviewRowData';

type SampleDataProps = {
  loadingSampleData: number[];
  sampleData: SampleData[];
  onRefreshSampleData: (index: number) => void;
};

const OperationSampleData = ({loadingSampleData, sampleData, onRefreshSampleData}: SampleDataProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.tailored_import.data_mapping.preview.input_title')}>
      {sampleData.map((sampleData, key) => (
        <PreviewRowData
          key={key}
          isLoading={loadingSampleData.includes(key)}
          isEmpty={sampleData === null}
          hasError={false}
          action={
            <IconButton
              disabled={loadingSampleData.includes(key)}
              icon={<RefreshIcon />}
              onClick={() => onRefreshSampleData(key)}
              title={translate('akeneo.tailored_import.data_mapping.preview.refresh')}
            />
          }
        >
          {formatSampleData(translate, sampleData)}
        </PreviewRowData>
      ))}
    </Preview>
  );
};

export {OperationSampleData};
