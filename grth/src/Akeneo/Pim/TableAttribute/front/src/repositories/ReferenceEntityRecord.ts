import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {ReferenceEntityRecord, ReferenceEntityIdentifierOrCode} from '../models';
import {RECORD_FETCHER_DEFAULT_LIMIT, RecordFetcher} from '../fetchers';

const search: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  props: {search?: string; page?: number; itemsPerPage?: number; channel: ChannelCode; locale: LocaleCode}
) => Promise<ReferenceEntityRecord[]> = async (
  router,
  referenceEntityIdentifier,
  {search = '', page = 0, itemsPerPage = RECORD_FETCHER_DEFAULT_LIMIT, channel, locale}
) => {
  return RecordFetcher.search(router, referenceEntityIdentifier, {search, page, itemsPerPage, channel, locale});
};

const ReferenceEntityRecordRepository = {
  search,
};

export {ReferenceEntityRecordRepository};
