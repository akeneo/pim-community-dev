import {Router} from '@akeneo-pim-community/shared';
import {ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../../models';

const firstResponse = {
  items: [
    {
      code: 'lanion00893335_2e73_41e3_ac34_763fb6a35107',
      completeness: {complete: 0, required: 0},
      identifier: 'city_lanion_00893335-2e73-41e3-ac34-763fb6a35107',
      image: null,
      labels: {en_US: 'Lanion'},
      reference_entity_identifier: 'city',
    },
    {
      code: 'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3',
      completeness: {complete: 0, required: 0},
      identifier: 'city_vannes_00bcf56a-2aa9-47c5-ac90-a973460b18a3',
      image: null,
      labels: {en_US: 'Vannes'},
      reference_entity_identifier: 'city',
    },
    {
      code: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
      completeness: {complete: 0, required: 0},
      identifier: 'city_nantes_00e3cffd-f60e-4a51-925b-d2952bd947e1',
      image: null,
      labels: {en_US: 'Nantes'},
      reference_entity_identifier: 'city',
    },
  ] as ReferenceEntityRecord[],
  matches_count: 760,
  total_count: 10004,
};

const secondResponse = {
  items: [
    {
      code: 'coueron00893335_2e73_41e3_ac34_763fb6a35107',
      completeness: {complete: 0, required: 0},
      identifier: 'city_coueron_00893335-2e73-41e3-ac34-763fb6a35107',
      image: null,
      labels: {en_US: 'Coueron'},
      reference_entity_identifier: 'city',
    },
    {
      code: 'brest00bcf56a_2aa9_47c5_ac90_a973460b18a3',
      completeness: {complete: 0, required: 0},
      identifier: 'city_brest_00bcf56a-2aa9-47c5-ac90-a973460b18a3',
      image: null,
      labels: {en_US: 'Brest'},
      reference_entity_identifier: 'city',
    },
  ] as ReferenceEntityRecord[],
  matches_count: 760,
  total_count: 10004,
};

const search = async (
  _router: Router,
  _ReferenceEntityIdentifierOrCode: ReferenceEntityIdentifierOrCode,
  {search = '', page = 0, itemsPerPage = 3}
): Promise<ReferenceEntityRecord[]> => {
  if (itemsPerPage === 0) return Promise.resolve([]);
  if (search !== '') return Promise.resolve([secondResponse.items[0]]);
  if (page === 0) return Promise.resolve(firstResponse.items);
  return Promise.resolve(secondResponse.items);
};

const RecordFetcher = {
  search,
};

export {RecordFetcher};
