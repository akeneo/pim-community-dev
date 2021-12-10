import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {ReferenceEntityRecord, ReferenceEntityIdentifierOrCode} from '../models';

type Response = {
  items: ReferenceEntityRecord[];
  matches_count: number;
  total_count: number;
};
export const RECORD_FETCHER_DEFAULT_LIMIT = 200;

const search: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  props: {search?: string; page?: number; itemsPerPage?: number; channel: ChannelCode; locale: LocaleCode}
) => Promise<ReferenceEntityRecord[]> = async (
  router,
  referenceEntityIdentifier,
  {search = '', page = 0, itemsPerPage = RECORD_FETCHER_DEFAULT_LIMIT, channel, locale}
) => {
  const url = router.generate('akeneo_reference_entities_record_index_rest', {
    referenceEntityIdentifier,
  });
  const body = {
    channel,
    locale,
    size: itemsPerPage,
    page,
    filters: [
      {field: 'reference_entity', operator: '=', value: referenceEntityIdentifier},
      {field: 'code_label', operator: '=', value: search},
      {field: 'code', operator: 'NOT IN', value: []},
    ],
  };

  const response = await fetch(url, {
    method: 'PUT',
    body: JSON.stringify(body),
  });

  const json: Response = await response.json();
  return json.items;
};

const RecordFetcher = {
  search,
};

export {RecordFetcher};
