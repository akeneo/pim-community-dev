import React from 'react';
import {FreeText as FreeTextModel, PropertyWithIdentifier} from '../../models';

type FreeTextProps = {
  freeTextProperty: FreeTextModel & PropertyWithIdentifier;
};

const FreeText: React.FC<FreeTextProps> = ({freeTextProperty}) => {
  return <>{freeTextProperty.string}</>;
};

export {FreeText};
