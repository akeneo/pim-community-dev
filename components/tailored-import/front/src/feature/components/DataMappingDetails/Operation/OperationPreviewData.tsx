import React from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, placeholderStyle, Preview} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {formatSampleData, PreviewData} from '../../../models';

const UnableToGeneratePreviewRow = styled.div`
  color: ${getColor('red', 100)};
`;

const PreviewContent = styled.div<{isLoading: boolean; isEmpty: boolean} & AkeneoThemedProps>`
  ${({isEmpty}) =>
    isEmpty &&
    css`
      color: ${getColor('grey', 100)};
    `}

  ${({isLoading}) => isLoading && placeholderStyle}
`;

type PreviewDataProps = {
  isLoading: boolean;
  previewData: PreviewData[];
  hasErrors: boolean;
};

const OperationPreviewData = ({isLoading, previewData, hasErrors}: PreviewDataProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.tailored_import.data_mapping.preview.title')}>
      {hasErrors ? (
        <Preview.Row>
          <UnableToGeneratePreviewRow>
            {translate('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')}
          </UnableToGeneratePreviewRow>
        </Preview.Row>
      ) : (
        previewData.map((previewData, key) => (
          <Preview.Row key={key}>
            <PreviewContent isLoading={isLoading} isEmpty={null === previewData}>
              {formatSampleData(translate, previewData)}
            </PreviewContent>
          </Preview.Row>
        ))
      )}
    </Preview>
  );
};

export {OperationPreviewData};
