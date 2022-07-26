import {useEffect, useState} from 'react';
import {useIsMounted, useRoute, useUserContext} from '@akeneo-pim-community/shared';
import {Family} from "../models";

const FAMILY_PAGE_SIZE = 25;

const useFamilies = (
  search: string,
  page: number,
  familyCodesToInclude: string[] | null,
  familyCodesToExclude: string[] | null,
  shouldFetch: boolean
) => {
  const [families, setFamilies] = useState<Family[]>([]);
  const [matchesCount, setMatchesCount] = useState<number>(0);
  const isMounted = useIsMounted();
  const familiesRoute = useRoute('pimee_tailored_import_get_families_action');
  const locale = useUserContext().get('catalogLocale');

  useEffect(() => {
    const fetchFamilies = async () => {
      const response = await fetch(familiesRoute, {
        body: JSON.stringify({
          search,
          page,
          limit: FAMILY_PAGE_SIZE,
          include_codes: familyCodesToInclude,
          exclude_codes: familyCodesToExclude,
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

      setFamilies(result.items);
      setMatchesCount(result.matches_count);
    };

    if (shouldFetch) void fetchFamilies();
  }, [shouldFetch, isMounted, familiesRoute, familyCodesToInclude, familyCodesToExclude, search, page, locale]);

  return [families, matchesCount] as const;
};

export {useFamilies, FAMILY_PAGE_SIZE};
