import {Router} from '../dependenciesTools';
import {httpGet} from './fetch';
import {Attribute} from '../models';

export const fetchAttributeByIdentifier = async (
  attributeIdentifier: string,
  router: Router
): Promise<Attribute | null> => {
  if (attributeIdentifier === undefined) {
    return null;
  }

  const url = router.generate('pim_enrich_attribute_rest_get', {
    identifier: attributeIdentifier,
  });
  const response = await httpGet(url);

  return response.status === 404 ? null : await response.json();
};

export const fetchAttributesByIdentifiers = async (
  attributeIdentifiers: string[],
  router: Router
): Promise<Attribute[]> => {
  const url = router.generate('pim_enrich_attribute_rest_index', {
    identifiers: attributeIdentifiers.join(','),
  });
  const response = await httpGet(url);

  return await response.json();
};
