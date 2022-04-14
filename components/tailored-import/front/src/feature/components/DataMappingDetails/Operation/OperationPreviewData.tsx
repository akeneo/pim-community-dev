import React from 'react';
import {Preview} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {formatSampleData, PreviewData} from '../../../models';
import {PreviewRowData} from './PreviewRowData';

type PreviewDataProps = {
  isLoading: boolean;
  previewData: PreviewData[];
  hasErrors: boolean;
};

const OperationPreviewData = ({isLoading, previewData, hasErrors}: PreviewDataProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.tailored_import.data_mapping.preview.output_title')}>
      {hasErrors ? (
        <PreviewRowData hasError={true}>
          {translate('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')}
        </PreviewRowData>
      ) : (
        previewData.map((previewData, key) => (
          <PreviewRowData key={key} hasError={false} isLoading={isLoading} isEmpty={null === previewData}>
            {formatSampleData(translate, previewData)}
          </PreviewRowData>
        ))
      )}
    </Preview>
  );
};

export {OperationPreviewData};
