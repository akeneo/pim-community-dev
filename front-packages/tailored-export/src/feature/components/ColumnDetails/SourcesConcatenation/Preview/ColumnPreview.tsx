import React, {Fragment} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontSize} from 'akeneo-design-system';
import {Format, Source} from '../../../../models';
import {
  AssociationTypeSourceElement,
  AttributeSourceElement,
  PropertySourceElement,
  TextElement,
} from './PreviewElement';

const PreviewTitle = styled.div`
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
  color: ${getColor('blue', 100)};
`;

const PreviewList = styled.div`
  overflow-wrap: break-word;
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
  sources: Source[];
  format: Format;
};

const ColumnPreview = ({sources, format}: ColumnPreviewProps) => {
  const translate = useTranslate();

  return (
    <ColumnPreviewContainer>
      <PreviewTitle>{translate('akeneo.tailored_export.column_details.concatenation.preview')}</PreviewTitle>
      <PreviewList>
        {format.elements
          .map(element => {
            if ('text' === element.type) {
              return <TextElement key={element.uuid}>{element.value}</TextElement>;
            }

            const source = sources.find(({uuid}) => uuid === element.value);

            switch (source?.type) {
              case 'attribute':
                return <AttributeSourceElement source={source} key={element.uuid} />;
              case 'property':
                return <PropertySourceElement source={source} key={element.uuid} />;
              case 'association_type':
                return <AssociationTypeSourceElement source={source} key={element.uuid} />;
              default:
                throw new Error(`Source with uuid ${element.value} not found`);
            }
          })
          .map((element, index) =>
            true === format.space_between && 0 < index ? <Fragment key={index}> {element}</Fragment> : element
          )}
      </PreviewList>
    </ColumnPreviewContainer>
  );
};

export {ColumnPreview};
