import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {RecordCode, ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../models';
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
  const records = await RecordFetcher.search(router, referenceEntityIdentifier, {
    search,
    page,
    itemsPerPage,
    channel,
    locale,
    codes,
  });

  records.forEach(record => {
    if (!(getKey(referenceEntityIdentifier, record.code) in referenceEntityRecordsCache)) {
      referenceEntityRecordsCache[getKey(referenceEntityIdentifier, record.code)] = record;
    }
  });

  const foundRecordCodes = records.map(record => record.code);
  const notFoundRecordCodes = (codes || []).filter(item => foundRecordCodes.indexOf(item) < 0);
  notFoundRecordCodes.forEach(recordCode => {
    referenceEntityRecordsCache[getKey(referenceEntityIdentifier, recordCode)] = null;
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

const getCachedByCode: (
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  code: RecordCode
) => ReferenceEntityRecord | null | undefined = (referenceEntityIdentifier, code) => {
  return referenceEntityRecordsCache[getKey(referenceEntityIdentifier, code)];
};

const ReferenceEntityRecordRepository = {
  search,
  findByCode,
  getCachedByCode,
};

export {ReferenceEntityRecordRepository};
