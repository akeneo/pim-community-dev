import {useCallback} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {AvailableSourcesResult, SourceOffset} from '../models';

const SUPPORTED_ATTRIBUTE_TYPES = [
  'pim_catalog_boolean',
  'pim_catalog_date',
  'pim_catalog_file',
  'pim_catalog_identifier',
  'pim_catalog_image',
  'pim_catalog_metric',
  'pim_catalog_number',
  'pim_catalog_multiselect',
  'pim_catalog_simpleselect',
  'pim_catalog_price_collection',
  'pim_catalog_textarea',
  'pim_catalog_text',
  'akeneo_reference_entity',
  'akeneo_reference_entity_collection',
  'pim_catalog_asset_collection',
];

const useAvailableSourcesFetcher = (searchValue: string, catalogLocale: string) => {
  const router = useRouter();

  return useCallback(
    async (offset: SourceOffset): Promise<AvailableSourcesResult> => {
      const route = router.generate('pimee_tailored_export_get_grouped_sources_action', {
        search: searchValue,
        'options[offset][system]': offset.system,
        'options[offset][association_type]': offset.association_type,
        'options[offset][attribute]': offset.attribute,
        'options[locale]': catalogLocale,
        'options[attributeTypes]': SUPPORTED_ATTRIBUTE_TYPES.join(','),
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

export {useAvailableSourcesFetcher};
