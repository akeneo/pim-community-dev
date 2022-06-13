import React from 'react';
import styled, {css} from 'styled-components';
import {Preview, Tag, Tags} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {formatSampleData, PreviewData} from '../../../models';
import {PreviewRowData} from './PreviewRowData';

const AnimatedPreview = styled(Preview)<{isDisplayed: boolean}>`
  border-top: none;
  overflow: hidden;
  transition: all 100ms ease-in-out;

  ${({isDisplayed}) =>
    isDisplayed
      ? css`
          max-height: 120px;
        `
      : css`
          border-bottom: none;
          padding-top: 0;
          padding-bottom: 0;
          max-height: 0;
        `}
`;

type PreviewDataProps = {
  isOpen: boolean;
  isLoading: boolean;
  previewData: PreviewData[] | undefined;
  hasErrors: boolean;
};

const OperationPreviewData = ({isLoading, previewData, isOpen, hasErrors}: PreviewDataProps) => {
  const translate = useTranslate();

  return (
    <AnimatedPreview isDisplayed={isOpen} title={translate('akeneo.tailored_import.data_mapping.preview.output_title')}>
      {hasErrors ? (
        <PreviewRowData hasError={true}>
          {translate('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')}
        </PreviewRowData>
      ) : (
        previewData?.map((data, key: number) => (
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
    </AnimatedPreview>
  );
};

export {OperationPreviewData};
