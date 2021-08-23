import {Router} from '@akeneo-pim-community/shared';
import {Attribute} from '../models/Attribute';
import {AttributeCode} from '../../../../../AssetManager/front/platform/model/structure/attribute';

const fetchAttribute = async (router: Router, attributeCode: AttributeCode): Promise<Attribute> => {
  const url = router.generate('pim_enrich_attribute_rest_get', {identifier: attributeCode});
  const response = await fetch(url);

  return (await response.json()) as Attribute;
};

export {fetchAttribute};
