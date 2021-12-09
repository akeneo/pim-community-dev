import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {ReferenceEntityRecord, ReferenceEntityIdentifierOrCode, RecordCode} from '../models';
import {RECORD_FETCHER_DEFAULT_LIMIT, RecordFetcher} from '../fetchers';

const referenceEntityRecordsCache: {[key: string]: ReferenceEntityRecord | null} = {};

const getKey: (referenceEntityIdentifier: ReferenceEntityIdentifierOrCode, recordCode: RecordCode) => string = (
  referenceEntityIdentifier,
  recordCode
) => {
  return `${referenceEntityIdentifier}-${recordCode}`;
};

const search: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  props: {search?: string; page?: number; itemsPerPage?: number; channel: ChannelCode; locale: LocaleCode}
) => Promise<ReferenceEntityRecord[]> = async (
  router,
  referenceEntityIdentifier,
  {search = '', page = 0, itemsPerPage = RECORD_FETCHER_DEFAULT_LIMIT, channel, locale}
) => {
  const records = await RecordFetcher.search(router, referenceEntityIdentifier, {
    search,
    page,
    itemsPerPage,
    channel,
    locale,
  });
  records.forEach(record => {
    if (!(getKey(referenceEntityIdentifier, record.code) in referenceEntityRecordsCache)) {
      referenceEntityRecordsCache[getKey(referenceEntityIdentifier, record.code)] = record;
    }
  });

  return records;
};

const findByCode: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  code: RecordCode
) => Promise<ReferenceEntityRecord | null> = async (router, referenceEntityIdentifier, code) => {
  if (getKey(referenceEntityIdentifier, code) in referenceEntityRecordsCache) {
    return referenceEntityRecordsCache[getKey(referenceEntityIdentifier, code)];
  }

  const record = await RecordFetcher.findByCode(router, referenceEntityIdentifier, code);
  referenceEntityRecordsCache[getKey(referenceEntityIdentifier, code)] = record;
  return record;
};

const ReferenceEntityRecordRepository = {
  search,
  findByCode,
};

export {ReferenceEntityRecordRepository};
