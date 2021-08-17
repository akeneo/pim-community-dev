import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontSize} from 'akeneo-design-system';
import {ColumnConfiguration} from '../../../../models';
import {
  AssociationTypeSourceElement,
  AttributeSourceElement,
  PropertySourceElement,
  StringElement,
} from './PreviewElement';

const PreviewTitle = styled.div`
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
  color: ${getColor('blue', 100)};
`;

const PreviewList = styled.div<{spaceBetween: boolean}>`
  display: flex;
  gap: ${({spaceBetween}) => (spaceBetween ? '5px' : '0px')};
`;

const ColumnPreviewContainer = styled.div`
  padding: 10px;
  background: ${getColor('blue', 10)};
  border-radius: 3px;
  border: 1px solid ${getColor('blue', 40)};
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type ColumnPreviewProps = {
  columnConfiguration: ColumnConfiguration;
};

const ColumnPreview = ({columnConfiguration}: ColumnPreviewProps) => {
  const translate = useTranslate();

  return (
    <ColumnPreviewContainer>
      <PreviewTitle>{translate('akeneo.tailored_export.column_details.concatenation.preview')}</PreviewTitle>
      <PreviewList spaceBetween={columnConfiguration.format.space_between ?? false}>
        {columnConfiguration.format.elements.map((element, index) => {
          if ('string' === element.type) {
            return <StringElement key={index}>{element.value}</StringElement>;
          }

          const source = columnConfiguration.sources.find(({uuid}) => uuid === element.value);

          switch (source?.type) {
            case 'attribute':
              return <AttributeSourceElement source={source} key={index} />;
            case 'property':
              return <PropertySourceElement source={source} key={index} />;
            case 'association_type':
              return <AssociationTypeSourceElement source={source} key={index} />;
            default:
              throw new Error(`Source with uuid ${element.value} not found`);
          }
        })}
      </PreviewList>
    </ColumnPreviewContainer>
  );
};

export {ColumnPreview};
