import React from 'react';
import styled, {css} from 'styled-components';
import {Preview, Tag, Tags} from 'akeneo-design-system';
import {Translate, useTranslate} from '@akeneo-pim-community/shared';
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

const getPreviewDataRow = (translate: Translate, key: number, isLoading: boolean, previewData: PreviewData) => {
  switch (previewData.type) {
    case 'string':
    case 'number':
    case 'date':
    case 'null':
      return (
        <PreviewRowData key={key} isLoading={isLoading} hasError={false} isEmpty={'null' === previewData.type}>
          {formatSampleData(translate, previewData.value)}
        </PreviewRowData>
      );
    case 'boolean':
      return (
        <PreviewRowData key={key} isLoading={isLoading} hasError={false}>
          <Tag tint="green">{String(previewData.value)}</Tag>
        </PreviewRowData>
      );
    case 'measurement':
      return (
        <PreviewRowData key={key} isLoading={isLoading} hasError={false}>
          {previewData.value} {previewData.unit}
        </PreviewRowData>
      );
    case 'array':
      return (
        <PreviewRowData key={key} isLoading={isLoading} hasError={false}>
          <Tags>
            {previewData.value.map((previewDataElement, key) => (
              <Tag key={key} tint="dark_blue">
                {formatSampleData(translate, previewDataElement)}
              </Tag>
            ))}
          </Tags>
        </PreviewRowData>
      );
    case 'invalid':
    default:
      return (
        <PreviewRowData key={key} isLoading={isLoading} hasError={true}>
          {translate('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')}
        </PreviewRowData>
      );
  }
};

type PreviewDataProps = {
  isOpen: boolean;
  isLoading: boolean;
  previewData: PreviewData[] | undefined;
  hasErrors: boolean;
};

const OperationPreviewData = ({isLoading, previewData, isOpen, hasErrors}: PreviewDataProps) => {
  const translate = useTranslate();

  if (undefined === previewData) {
    return null;
  }

  return (
    <AnimatedPreview isDisplayed={isOpen} title={translate('akeneo.tailored_import.data_mapping.preview.output_title')}>
      {hasErrors ? (
        <PreviewRowData hasError={true}>
          {translate('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')}
        </PreviewRowData>
      ) : (
        previewData.map((previewData, key) => getPreviewDataRow(translate, key, isLoading, previewData))
      )}
    </AnimatedPreview>
  );
};

export {OperationPreviewData};
