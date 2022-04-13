import React from "react";
import styled, {css} from "styled-components";
import {getColor, placeholderStyle, Preview} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/shared";
import {formatSampleData, PreviewData} from "../../../models";
import {AkeneoThemedProps} from "akeneo-design-system/lib/theme/theme";
import {ValidationError} from "@akeneo-pim-community/shared/lib/models/validation-error";

const UnableToGeneratePreviewRow = styled.div`
  color: ${getColor('red', 100)};
`;

const PreviewContent = styled.div<{isLoading: boolean, isEmpty: boolean} & AkeneoThemedProps>`
  ${({isEmpty}) => isEmpty && css`
    color: ${getColor('grey', 100)};
  `}

  ${({isLoading}) => isLoading && placeholderStyle}
`;

type PreviewDataProps = {
  isLoading: boolean;
  previewData: PreviewData[];
  validationErrors: ValidationError[];
};

const OperationPreviewData = ({isLoading, previewData, validationErrors}: PreviewDataProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.tailored_import.data_mapping.preview.title')}>
      {validationErrors.length > 0 ? (
        <Preview.Row>
          <UnableToGeneratePreviewRow>
            Unable to generate preview
          </UnableToGeneratePreviewRow>
        </Preview.Row>
      ): previewData.map((previewData, key) => (
        <Preview.Row key={key}>
          <PreviewContent isLoading={isLoading} isEmpty={null === previewData}>
            {formatSampleData(translate, previewData)}
          </PreviewContent>
        </Preview.Row>
      ))}
    </Preview>
  );
}

export {OperationPreviewData};
