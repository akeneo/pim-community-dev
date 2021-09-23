import {useState, useEffect} from 'react';
import {useIsMounted, useRoute, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../models';

const OPTION_COLLECTION_PAGE_SIZE = 25;

const useAttributeOptions = (attributeCode: string, search: string, page: number, optionCodesToExclude: string[]) => {
  const [attributeOptions, setAttributeOptions] = useState<AttributeOption[]>([]);
  const [matchesCount, setMatchesCount] = useState<number>(0);
  const isMounted = useIsMounted();
  const attributeOptionsRoute = useRoute('pimee_tailored_export_get_attribute_options_action', {
    attribute_code: attributeCode,
  });
  const locale = useUserContext().get('catalogLocale');

  useEffect(() => {
    const fetchAttributeOptions = async () => {
      const response = await fetch(attributeOptionsRoute, {
        body: JSON.stringify({
          search,
          page,
          limit: OPTION_COLLECTION_PAGE_SIZE,
          exclude_codes: optionCodesToExclude,
          locale,
        }),
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
      });

      const result = await response.json();

      if (!isMounted()) return;

      setAttributeOptions(result.items);
      setMatchesCount(result.matches_count);
    };

    fetchAttributeOptions();
  }, [isMounted, attributeOptionsRoute, optionCodesToExclude, search, page, locale]);

  return [attributeOptions, matchesCount] as const;
};

export {useAttributeOptions, OPTION_COLLECTION_PAGE_SIZE};
