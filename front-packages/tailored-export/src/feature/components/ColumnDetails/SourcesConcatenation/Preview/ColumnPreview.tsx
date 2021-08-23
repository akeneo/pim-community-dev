import React, {Fragment} from 'react';
import {Preview} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ConcatElement, Format, Source} from '../../../../models';
import {AssociationTypeSourceElement, AttributeSourceElement, PropertySourceElement} from './PreviewElement';

const getPreviewElement = (element: ConcatElement, sources: Source[]) => {
  if ('text' === element.type) {
    return <Preview.Highlight key={element.uuid}>{element.value}</Preview.Highlight>;
  }

  const source = sources.find(({uuid}) => uuid === element.value);

  if (undefined === source) {
    throw new Error(`Source with uuid ${element.value} not found`);
  }

  switch (source.type) {
    case 'attribute':
      return <AttributeSourceElement source={source} key={element.uuid} />;
    case 'property':
      return <PropertySourceElement source={source} key={element.uuid} />;
    case 'association_type':
      return <AssociationTypeSourceElement source={source} key={element.uuid} />;
    default:
      throw new Error('Invalid source type');
  }
};

type ColumnPreviewProps = {
  sources: Source[];
  format: Format;
};

const ColumnPreview = ({sources, format}: ColumnPreviewProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.tailored_export.column_details.concatenation.preview')}>
      {format.elements
        .map(element => getPreviewElement(element, sources))
        .map((element, index) =>
          format.space_between && 0 < index ? <Fragment key={index}> {element}</Fragment> : element
        )}
    </Preview>
  );
};

export {ColumnPreview};
