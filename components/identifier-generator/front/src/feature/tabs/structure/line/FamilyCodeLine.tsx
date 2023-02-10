import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';

const FamilyCodeLine: React.FC = () => {
  const translate = useTranslate();

  return <>{translate('pim_identifier_generator.structure.settings.family.title')}</>;
};

export {FamilyCodeLine};
