import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {RecordCode, ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../models';
import {RecordFetcher} from '../fetchers';

type Params = {
  search?: string;
  page?: number;
  itemsPerPage?: number;
  channel: ChannelCode;
  locale: LocaleCode;
  codes?: RecordCode[];
};

type CallParam = Params & {
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode;
};

const referenceEntityRecordsCache: {[key: string]: ReferenceEntityRecord | null} = {};
const referenceEntityRecordsCalls: {method: string; params: CallParam; result: Promise<ReferenceEntityRecord[]>}[] = [];

const clearCache = () => {
  referenceEntityRecordsCalls.splice(0, referenceEntityRecordsCalls.length);
  Object.entries(referenceEntityRecordsCache).forEach(([key]) => delete referenceEntityRecordsCache[key]);
};

const getKey: (referenceEntityIdentifier: ReferenceEntityIdentifierOrCode, recordCode: RecordCode) => string = (
  referenceEntityIdentifier,
  recordCode
) => {
  return `${referenceEntityIdentifier}-${recordCode}`;
};

const getCachedCall = (
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  params: Params
): Promise<ReferenceEntityRecord[]> | undefined => {
  const found = referenceEntityRecordsCalls.find(item => {
    const {search, page, itemsPerPage, codes} = params;
    return (
      referenceEntityIdentifier === item.params.referenceEntityIdentifier &&
      search === item.params.search &&
      page === item.params.page &&
      itemsPerPage === item.params.itemsPerPage &&
      JSON.stringify(codes) === JSON.stringify(item.params.codes)
    );
  });
  return found?.result;
};

const saveCall = (
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  params: Params,
  result: Promise<ReferenceEntityRecord[]>
): void => {
  referenceEntityRecordsCalls.push({
    method: 'search',
    params: {
      ...params,
      referenceEntityIdentifier,
    },
    result,
  });
};

const search: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  props: Params
) => Promise<ReferenceEntityRecord[]> = async (router, referenceEntityIdentifier, props) => {
  let codesToFetch: RecordCode[] | undefined = undefined;
  const alreadyFetchedRecords: ReferenceEntityRecord[] = [];

  // check if promise has already been called or if it is already loading
  const cachedCall = getCachedCall(referenceEntityIdentifier, props);
  if (cachedCall) return cachedCall;

  if (props.codes) {
    const nonFetchedRecords: RecordCode[] = [];

    props.codes.forEach(recordCode => {
      const record = referenceEntityRecordsCache[getKey(referenceEntityIdentifier, recordCode)];
      if (typeof record !== 'undefined') {
        if (record !== null) {
          alreadyFetchedRecords.push(record);
        }
      } else {
        nonFetchedRecords.push(recordCode);
      }
    });
    if (nonFetchedRecords.length) {
      codesToFetch = nonFetchedRecords;
    } else {
      return alreadyFetchedRecords;
    }
  }

  const recordsPromise = RecordFetcher.search(router, referenceEntityIdentifier, {...props, codes: codesToFetch});
  saveCall(referenceEntityIdentifier, props, recordsPromise);

  const records = await recordsPromise;

  records.forEach(record => {
    if (!(getKey(referenceEntityIdentifier, record.code) in referenceEntityRecordsCache)) {
      referenceEntityRecordsCache[getKey(referenceEntityIdentifier, record.code)] = record;
    }
  });

  const foundRecordCodes = records.map(record => record.code);
  const notFoundRecordCodes = (codesToFetch || []).filter(item => foundRecordCodes.indexOf(item) < 0);
  notFoundRecordCodes.forEach(recordCode => {
    referenceEntityRecordsCache[getKey(referenceEntityIdentifier, recordCode)] = null;
  });

  return Promise.resolve(records.concat(alreadyFetchedRecords));
};

const findByCode: (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  code: RecordCode
) => Promise<ReferenceEntityRecord | null> = async (router, referenceEntityIdentifier, code) => {
  if (!(getKey(referenceEntityIdentifier, code) in referenceEntityRecordsCache)) {
    referenceEntityRecordsCache[getKey(referenceEntityIdentifier, code)] = await RecordFetcher.findByCode(
      router,
      referenceEntityIdentifier,
      code
    );
  }

  return referenceEntityRecordsCache[getKey(referenceEntityIdentifier, code)];
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
  clearCache,
};

export {ReferenceEntityRecordRepository};
