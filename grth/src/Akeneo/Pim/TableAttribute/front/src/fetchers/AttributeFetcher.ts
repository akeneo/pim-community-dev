import {Router} from '@akeneo-pim-community/shared';
import {Attribute, AttributeCode} from '../models';

const fetchAttribute = async (router: Router, attributeCode: AttributeCode): Promise<Attribute> => {
  const url = router.generate('pim_enrich_attribute_rest_get', {identifier: attributeCode});
  const response = await fetch(url);

  return (await response.json()) as Attribute;
};

const AttributeFetcher = {
  fetch: fetchAttribute,
};

export {AttributeFetcher};
