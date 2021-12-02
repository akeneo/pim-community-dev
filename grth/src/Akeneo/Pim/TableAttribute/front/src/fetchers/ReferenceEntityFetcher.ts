import {Router} from '@akeneo-pim-community/shared';
import {ReferenceEntity} from '../models';

type Response = {
  items: ReferenceEntity[];
  total: number;
};

const fetchAll = async (router: Router): Promise<ReferenceEntity[]> => {
  const url = router.generate('akeneo_reference_entities_reference_entity_index_rest');
  const response = await fetch(url);

  const json: Response = await response.json();
  return json.items;
};

const ReferenceEntityFetcher = {
  fetchAll,
};

export {ReferenceEntityFetcher};
