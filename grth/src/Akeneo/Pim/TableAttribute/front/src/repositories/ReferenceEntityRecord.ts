import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {RecordCode, ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../models';
import {RecordFetcher} from '../fetchers';

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
) => Promise<ReferenceEntityRecord[]> = async (router, referenceEntityIdentifier, props) => {
  let codesToFetch: RecordCode[] | undefined = undefined;
  const alreadyFetchedRecords: ReferenceEntityRecord[] = [];
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

  const records = await RecordFetcher.search(router, referenceEntityIdentifier, {...props, codes: codesToFetch});

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

  return records.concat(alreadyFetchedRecords);
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
};

export {ReferenceEntityRecordRepository};
