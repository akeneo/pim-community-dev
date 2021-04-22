import {Button, useBooleanState} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {useEffect} from 'react';

const Column = ({jobCode}: {jobCode: string}) => {
  const [test, open, close] = useBooleanState();
  const translate = useTranslate();
  const route = useRoute('pim_enrich_measures_rest_index');

  useEffect(() => {
    fetch(route);
  }, []);

  return (
    <Button level="secondary" onClick={test ? close : open}>
      {translate('pim_common.edit')}: {jobCode}! {test ? 'nice' : 'cool'}
    </Button>
  );
};

export {Column};
