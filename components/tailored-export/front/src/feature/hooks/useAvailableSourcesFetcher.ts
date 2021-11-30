import {useCallback} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {AvailableSourcesResult, SourceOffset} from '../models';
import {useEntityType} from '../contexts/EntityTypeContext';

const useAvailableSourcesFetcher = (searchValue: string, catalogLocale: string) => {
  const router = useRouter();
  const entityType = useEntityType();

  return useCallback(
    async (offset: SourceOffset): Promise<AvailableSourcesResult> => {
      const route = router.generate(`pimee_tailored_export_get_${entityType}_grouped_sources_action`, {
        search: searchValue,
        'options[offset][system]': offset.system,
        'options[offset][association_type]': offset.association_type,
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
    [catalogLocale, router, searchValue, entityType]
  );
};

export {useAvailableSourcesFetcher};
