import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Index = ({jobCode}: {jobCode: string}) => {
  const [test, open, close] = useBooleanState();
  const translate = useTranslate();

  return (
    <Button level="secondary" onClick={test ? close : open}>
      {translate('pim_common.edit')}: {jobCode}! {test ? 'nice' : 'cool'}
    </Button>
  );
};

export default Index;
