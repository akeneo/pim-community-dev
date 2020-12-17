import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy';

const Test = () => {
  const translate = useTranslate();

  return <div>That's so awesome 🎉 {translate('pim_common.close')}</div>;
};

export default Test;
