import React from "react";
import styled, {css} from "styled-components";
import {AkeneoThemedProps, getColor, IconButton, placeholderStyle, Preview, RefreshIcon} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/shared";
import {formatSampleData, SampleData} from "../../../models";

const PreviewContent = styled.div<{isLoading: boolean, isEmpty: boolean} & AkeneoThemedProps>`
  ${({isEmpty}) => isEmpty && css`
    color: ${getColor('grey', 100)};
  `}

  ${({isLoading}) => isLoading && placeholderStyle}
`;

type SampleDataProps = {
  loadingSampleData: number[];
  sampleData: SampleData[];
  onRefreshSampleData: (index: number) => void;
};

const OperationSampleData = ({loadingSampleData, sampleData, onRefreshSampleData}: SampleDataProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.tailored_import.data_mapping.preview.title')}>
      {sampleData.map((sampleData, key) => (
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
          <PreviewContent isLoading={loadingSampleData.includes(key)} isEmpty={sampleData === null}>
            {formatSampleData(translate, sampleData)}
          </PreviewContent>
        </Preview.Row>
      ))}
    </Preview>
  );
}

export {OperationSampleData};
