import {useRouter} from '@akeneo-pim-community/shared';

const SUPPORTED_ATTRIBUTE_TYPES = [
  'pim_catalog_boolean',
  // 'pim_catalog_date',
  // 'pim_catalog_file',
  'pim_catalog_identifier',
  // 'pim_catalog_image',
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

  return async (page: number) => {
    const route = router.generate('pimee_tailored_export_get_grouped_sources_action', {
      search: searchValue,
      'options[page]': String(page),
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
  };
};

export {useAvailableSourcesFetcher};
