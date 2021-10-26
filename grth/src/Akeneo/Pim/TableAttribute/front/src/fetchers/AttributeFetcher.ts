import {Router} from '@akeneo-pim-community/shared';
import {Attribute, AttributeCode, AttributeType} from '../models';

const fetchAttribute = async (router: Router, attributeCode: AttributeCode): Promise<Attribute> => {
  const url = router.generate('pim_enrich_attribute_rest_get', {identifier: attributeCode});
  const response = await fetch(url);

  return (await response.json()) as Attribute;
};

const fetchAttributesByTypes = async (router: Router, attributeTypes: AttributeType[]): Promise<Attribute[]> => {
  const url = router.generate('pim_enrich_attribute_rest_index', {types: attributeTypes});
  const response = await fetch(url);

  return (await response.json()) as Attribute[];
}

const AttributeFetcher = {
  fetch: fetchAttribute,
  fetchByTypes: fetchAttributesByTypes,
};

export {AttributeFetcher};
