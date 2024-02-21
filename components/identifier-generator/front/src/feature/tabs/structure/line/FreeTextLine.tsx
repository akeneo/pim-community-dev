import React from 'react';
import {FreeText} from '../../../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type FreeTextLineProps = {
  freeTextProperty: FreeText;
};

const FreeTextLine: React.FC<FreeTextLineProps> = ({freeTextProperty}) => {
  const translate = useTranslate();

  return (
    <>
      {freeTextProperty.string.length
        ? freeTextProperty.string
        : translate('pim_identifier_generator.structure.property_type.free_text')}
    </>
  );
};

export {FreeTextLine};
