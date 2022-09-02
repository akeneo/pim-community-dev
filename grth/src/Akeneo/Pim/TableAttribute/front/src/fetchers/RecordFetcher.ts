import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {RecordCode, ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../models';

type Response = {
  items: ReferenceEntityRecord[];
  matches_count: number;
  total_count: number;
};
export const RECORD_FETCHER_DEFAULT_LIMIT = 200;

const search: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  props: {
    search?: string;
    page?: number;
    itemsPerPage?: number;
    channel: ChannelCode;
    locale: LocaleCode;
    codes?: RecordCode[];
  }
) => Promise<ReferenceEntityRecord[]> = async (
  router,
  referenceEntityIdentifier,
  {search = '', page = 0, itemsPerPage = RECORD_FETCHER_DEFAULT_LIMIT, channel, locale, codes}
) => {
  const url = router.generate('akeneo_reference_entities_record_index_rest', {
    referenceEntityIdentifier,
  });

  const body: {
    channel: ChannelCode;
    locale: LocaleCode;
    size: number;
    page: number;
    filters: {field: string; operator: string; value: any}[];
  } = {
    channel,
    locale,
    size: itemsPerPage,
    page,
    filters: [
      {field: 'reference_entity', operator: '=', value: referenceEntityIdentifier},
      {field: 'code_label', operator: '=', value: search},
    ],
  };

  if (codes) {
    if (codes.length === 0) return [];
    body.filters.push({field: 'code', operator: 'IN', value: codes});
  }

  const response = await fetch(url, {
    method: 'PUT',
    body: JSON.stringify(body),
  });

  const json: Response = await response.json();
  return json.items;
};

const findByCode: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  recordCode: RecordCode
) => Promise<ReferenceEntityRecord | null> = async (router, referenceEntityIdentifier, recordCode) => {
  const url = router.generate('akeneo_reference_entities_record_get_rest', {
    referenceEntityIdentifier,
    recordCode,
  });

  const response = await fetch(url);
  if (response.status === 404) {
    return null;
  }
  return await response.json();
};

const RecordFetcher = {
  search,
  findByCode,
};

export {RecordFetcher};
