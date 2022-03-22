import React from 'react';
import {LocaleRepository} from '../repositories';
import {Locale, useRouter} from '@akeneo-pim-community/shared';

const useActivatedLocales: () => Locale[] | undefined = () => {
  const router = useRouter();
  const [activatedLocales, setActivatedLocales] = React.useState<Locale[]>();

  React.useEffect(() => {
    LocaleRepository.findActivated(router).then((activeLocales: Locale[]) => setActivatedLocales(activeLocales));
  }, [router]);

  return activatedLocales;
};

export {useActivatedLocales};
