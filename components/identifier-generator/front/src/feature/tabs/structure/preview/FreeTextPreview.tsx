import React from 'react';
import {FreeText} from '../../../models';
import {Preview} from 'akeneo-design-system';

type FreeTextPreviewProps = {
  property: FreeText;
};

const FreeTextPreview: React.FC<FreeTextPreviewProps> = ({property}) => {
  return <Preview.Highlight>{property.string || ' '}</Preview.Highlight>;
};

export {FreeTextPreview};
