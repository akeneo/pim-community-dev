import {useRouter} from '@akeneo-pim-community/shared';

const useAvailableSourcesFetcher = (searchValue: string, catalogLocale: string) => {
  const router = useRouter();

  return async (page: number) => {
    const route = router.generate('pimee_tailored_export_get_grouped_sources_action', {
      search: searchValue,
      'options[page]': String(page),
      'options[locale]': catalogLocale,
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
