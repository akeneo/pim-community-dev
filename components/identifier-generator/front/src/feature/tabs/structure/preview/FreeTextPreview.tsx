import React from 'react';
import {FreeText, TextTransformation} from '../../../models';
import {Preview} from 'akeneo-design-system';
import {useTextTransformation} from '../../../hooks';

type FreeTextPreviewProps = {
  property: FreeText;
  textTransformation: TextTransformation;
};

const FreeTextPreview: React.FC<FreeTextPreviewProps> = ({property, textTransformation}) => {
  const transformedProperty = useTextTransformation(property.string || ' ', textTransformation);

  return <Preview.Highlight>{transformedProperty}</Preview.Highlight>;
};

export {FreeTextPreview};
