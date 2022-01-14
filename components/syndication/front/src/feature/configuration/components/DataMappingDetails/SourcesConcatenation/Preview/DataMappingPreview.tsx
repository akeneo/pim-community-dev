import React, {Fragment} from 'react';
import {Preview} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ConcatElement, ConcatFormat, Source} from '../../../../models';
import {
  AssociationTypeSourceElement,
  AttributeSourceElement,
  PropertySourceElement,
  StaticSourceElement,
} from './PreviewElement';

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
    case 'static':
      return <StaticSourceElement source={source} key={element.uuid} />;
    case 'association_type':
      return <AssociationTypeSourceElement source={source} key={element.uuid} />;
    default:
      throw new Error('Invalid source type');
  }
};

type DataMappingPreviewProps = {
  sources: Source[];
  format: ConcatFormat;
};

const DataMappingPreview = ({sources, format}: DataMappingPreviewProps) => {
  const translate = useTranslate();

  return (
    <Preview title={translate('akeneo.syndication.data_mapping_details.concatenation.preview')}>
      {format.elements
        .map(element => getPreviewElement(element, sources))
        .map((element, index) =>
          format.space_between && 0 < index ? <Fragment key={index}> {element}</Fragment> : element
        )}
    </Preview>
  );
};

export {DataMappingPreview};
