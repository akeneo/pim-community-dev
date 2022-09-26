import {useState, useEffect} from 'react';
import {useIsMounted, useRoute, useUserContext} from '@akeneo-pim-community/shared';
import {Record} from '../models';

const RECORDS_COLLECTION_PAGE_SIZE = 25;

const useRecords = (
  referenceDataName: string,
  search: string,
  page: number,
  recordCodesToInclude: string[] | null,
  recordCodesToExclude: string[] | null,
  shouldFetch: boolean
) => {
  const [records, setRecords] = useState<Record[]>([]);
  const [matchesCount, setMatchesCount] = useState<number>(0);
  const isMounted = useIsMounted();
  const locale = useUserContext().get('catalogLocale');
  const channel = useUserContext().get('catalogScope');
  const recordsRoute = useRoute('pimee_tailored_import_get_records_action', {
    reference_entity_code: referenceDataName,
  });

  useEffect(() => {
    const fetchRecords = async () => {
      const response = await fetch(recordsRoute, {
        body: JSON.stringify({
          search,
          page,
          limit: RECORDS_COLLECTION_PAGE_SIZE,
          include_codes: recordCodesToInclude,
          exclude_codes: recordCodesToExclude,
          locale,
          channel,
        }),
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
      });

      const result = await response.json();

      if (!isMounted()) return;

      setRecords(result.items);
      setMatchesCount(result.matches_count);
    };

    if (shouldFetch) void fetchRecords();
  }, [shouldFetch, isMounted, recordsRoute, recordCodesToInclude, recordCodesToExclude, search, page, locale, channel]);

  return [records, matchesCount] as const;
};

export {useRecords, RECORDS_COLLECTION_PAGE_SIZE};
