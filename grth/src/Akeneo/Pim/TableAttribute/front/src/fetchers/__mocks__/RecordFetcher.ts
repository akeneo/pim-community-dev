import {ChannelCode, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {RecordCode, ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../../models';

// For the test, please use the default pagination size to 3 items

const lannion: ReferenceEntityRecord = {
  code: 'lannion00893335_2e73_41e3_ac34_763fb6a35107',
  completeness: {complete: 3, required: 4},
  identifier: 'city_lannion_00893335-2e73-41e3-ac34-763fb6a35107',
  image: {
    extension: 'jpeg',
    filePath: 'c/e/9/1/Lannion.jpg',
    mimeType: 'image/jpeg',
    originalFilename: 'lannion.jpg',
    size: 5396,
  },
  labels: {en_US: 'Lannion'},
  reference_entity_identifier: 'city',
  values: {},
};
const vannes: ReferenceEntityRecord = {
  code: 'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3',
  completeness: {complete: 0, required: 0},
  identifier: 'city_vannes_00bcf56a-2aa9-47c5-ac90-a973460b18a3',
  image: null,
  labels: {en_US: 'Vannes'},
  reference_entity_identifier: 'city',
  values: {},
};
const nantes: ReferenceEntityRecord = {
  code: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
  completeness: {complete: 0, required: 0},
  identifier: 'city_nantes_00e3cffd-f60e-4a51-925b-d2952bd947e1',
  image: null,
  labels: {en_US: 'Nantes'},
  reference_entity_identifier: 'city',
  values: {},
};
const coueron: ReferenceEntityRecord = {
  code: 'coueron00893335_2e73_41e3_ac34_763fb6a35107',
  completeness: {complete: 0, required: 0},
  identifier: 'city_coueron_00893335-2e73-41e3-ac34-763fb6a35107',
  image: null,
  labels: {en_US: 'Coueron'},
  reference_entity_identifier: 'city',
  values: {},
};
const brest: ReferenceEntityRecord = {
  code: 'brest00bcf56a_2aa9_47c5_ac90_a973460b18a3',
  completeness: {complete: 0, required: 0},
  identifier: 'city_brest_00bcf56a-2aa9-47c5-ac90-a973460b18a3',
  image: null,
  labels: {en_US: 'Brest'},
  reference_entity_identifier: 'city',
  values: {},
};
export const referenceEntityRecordMocks = [lannion, vannes, nantes, coueron, brest];

const search: (
  _router: Router,
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
  _router,
  referenceEntityIdentifier,
  {search = '', page = 0, itemsPerPage = 3, codes}
) => {
  if (referenceEntityIdentifier === 'empty_reference_entity') {
    return new Promise(resolve => resolve([]));
  }
  if (codes) {
    return new Promise(resolve => resolve(referenceEntityRecordMocks.filter(item => codes.includes(item.code))));
  }
  const filteredItems = referenceEntityRecordMocks.filter(item =>
    item.code.toLowerCase().includes(search.toLowerCase())
  );
  return new Promise(resolve => resolve(filteredItems.slice(page * itemsPerPage, (page + 1) * itemsPerPage)));
};

const findByCode: (
  _router: Router,
  _referenceEntityIdentifier: ReferenceEntityIdentifierOrCode,
  recordCode: RecordCode
) => Promise<ReferenceEntityRecord | null> = async (_router, _referenceEntityIdentifier, recordCode) => {
  return new Promise(resolve => resolve(referenceEntityRecordMocks.find(item => item.code === recordCode) || null));
};

const RecordFetcher = {
  search,
  findByCode,
};

export {RecordFetcher};
