import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy';

const Test = () => {
  const translate = useTranslate();

  return <div>Coucou {translate('pim_common.close')}</div>;
};

export {Test};
