import React from 'react';
import {AutoNumber, PROPERTY_NAMES} from '../../../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type AutoNumberLineProps = {
  property: AutoNumber;
};

const AutoNumberLine: React.FC<AutoNumberLineProps> = () => {
  const translate = useTranslate();

  return <>{translate(`pim_identifier_generator.structure.settings.${PROPERTY_NAMES.AUTO_NUMBER}.title`)}</>;
};

export {AutoNumberLine};
