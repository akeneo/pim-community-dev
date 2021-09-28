import {useState, useEffect} from 'react';
import {useIsMounted, useRoute, useUserContext} from '@akeneo-pim-community/shared';
import {Record} from './Record';

const RECORD_PAGE_SIZE = 25;

const useRecords = (
  referenceEntityCode: string,
  search: string,
  page: number,
  optionCodesToInclude: string[] | null,
  optionCodesToExclude: string[] | null
) => {
  const [records, setRecords] = useState<Record[]>([]);
  const [matchesCount, setMatchesCount] = useState<number>(0);
  const isMounted = useIsMounted();
  const locale = useUserContext().get('catalogLocale');
  const channel = useUserContext().get('catalogScope');
  const getRecordsRoute = useRoute('pimee_tailored_export_get_records_action', {
    reference_entity_code: referenceEntityCode,
  });

  useEffect(() => {
    const fetchRecords = async () => {
      const response = await fetch(getRecordsRoute, {
        body: JSON.stringify({
          search,
          page,
          limit: RECORD_PAGE_SIZE,
          include_codes: optionCodesToInclude,
          exclude_codes: optionCodesToExclude,
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

    void fetchRecords();
  }, [isMounted, getRecordsRoute, optionCodesToInclude, optionCodesToExclude, search, page, channel, locale]);

  return [records, matchesCount] as const;
};

export {useRecords, RECORD_PAGE_SIZE};
