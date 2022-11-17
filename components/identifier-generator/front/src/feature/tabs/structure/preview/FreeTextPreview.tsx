import React from 'react';
import {FreeText} from '../../../models';
import {Preview} from 'akeneo-design-system';

type FreeTextPreviewProps = {
  freeTextProperty: FreeText;
};

const FreeTextPreview: React.FC<FreeTextPreviewProps> = ({freeTextProperty}) => {
  return <Preview.Highlight>{freeTextProperty.string}</Preview.Highlight>;
};

export {FreeTextPreview};
