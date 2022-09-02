import {useCallback} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {AvailableTargetsResult, TargetOffset} from '../models';

const useAvailableTargetsFetcher = (searchValue: string, catalogLocale: string) => {
  const router = useRouter();

  return useCallback(
    async (offset: TargetOffset): Promise<AvailableTargetsResult> => {
      const route = router.generate(`pimee_tailored_import_get_grouped_targets_action`, {
        search: searchValue,
        'options[offset][system]': offset.system,
        'options[offset][attribute]': offset.attribute,
        'options[locale]': catalogLocale,
      });

      const response = await fetch(route, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return await response.json();
    },
    [catalogLocale, router, searchValue]
  );
};

export {useAvailableTargetsFetcher};
