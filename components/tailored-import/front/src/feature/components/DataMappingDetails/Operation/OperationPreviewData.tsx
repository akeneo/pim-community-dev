import React from 'react';
import {Preview, Tag, Tags} from 'akeneo-design-system';
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
        previewData.map((data, key) => (
          <PreviewRowData key={key} hasError={false} isLoading={isLoading} isEmpty={null === data}>
            {Array.isArray(data) ? (
              <Tags>
                {data.map((previewDataElement, key) => (
                  <Tag key={key} tint="dark_blue">
                    {formatSampleData(translate, previewDataElement)}
                  </Tag>
                ))}
              </Tags>
            ) : (
              formatSampleData(translate, data)
            )}
          </PreviewRowData>
        ))
      )}
    </Preview>
  );
};

export {OperationPreviewData};
