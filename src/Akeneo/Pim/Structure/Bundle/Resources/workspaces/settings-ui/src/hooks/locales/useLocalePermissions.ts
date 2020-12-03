import {useEffect, useState} from 'react';
import {useSecurity} from '@akeneo-pim-community/legacy-bridge';

enum LocalePermissions {
  Index = 'pim_enrich_locale_index',
  //Edit = 'pim_enrich_locale_edit',
}

type LocalePermissionsState = {
  indexGranted: boolean;
};

const useLocalePermissions = (): LocalePermissionsState => {
  const [state, setState] = useState<LocalePermissionsState>({
    indexGranted: false,
  });
  const {isGranted} = useSecurity();

  useEffect(() => {
    if (typeof isGranted === 'function') {
      setState({
        indexGranted: isGranted(LocalePermissions.Index),
      });
    }
  }, [isGranted]);

  return state;
};

export {useLocalePermissions};
