import React from 'react';
import {FreeText} from '../../models';

type FreeTextLineProps = {
  freeTextProperty: FreeText;
};

const FreeTextLine: React.FC<FreeTextLineProps> = ({freeTextProperty}) => {
  return <>{freeTextProperty.string}</>;
};

export {FreeTextLine};
