import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';


const FamilyCodeLine: React.FC = () => {
  const translate = useTranslate();

  return <div>{translate('pim_identifier_generator.structure.property_type.family')}</div>;
};

export {FamilyCodeLine};
