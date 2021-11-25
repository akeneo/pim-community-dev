import {Router} from '@akeneo-pim-community/shared';
import {Attribute, AttributeCode, AttributeType} from '../models';

const fetchAttribute = async (router: Router, attributeCode: AttributeCode): Promise<Attribute> => {
  const url = router.generate('pim_enrich_attribute_rest_get', {identifier: attributeCode});
  const response = await fetch(url);

  return (await response.json()) as Attribute;
};

// There are other available parameters, but they are not implemented for now.
// @see vendor/akeneo/pim-community-dev/src/Akeneo/Pim/Structure/Bundle/Controller/InternalApi/AttributeController.php
export type AttributeFetcherIndexParams = {
  types?: AttributeType[];
  search?: string;
};

const query = async (router: Router, params: AttributeFetcherIndexParams): Promise<Attribute[]> => {
  const url = router.generate('pim_enrich_attribute_rest_index', params);
  const response = await fetch(url);

  return (await response.json()) as Attribute[];
};

const AttributeFetcher = {
  fetch: fetchAttribute,
  query: query,
};

export {AttributeFetcher};
