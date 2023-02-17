import {useTranslate} from '@akeneo-pim-community/shared';
import React from 'react';

const SimpleSelectPropertyLine: React.FC = () => {
  const translate = useTranslate();

  return <>{translate('pim_identifier_generator.structure.settings.simple_select.title')}</>;
};

export {SimpleSelectPropertyLine};
