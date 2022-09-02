import {Router, LocaleCode, RouteParams} from '@akeneo-pim-community/shared';
import {Attribute, AttributeCode, AttributeType, AttributeWithOptions} from '../models';

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

const findAttributeWithOptions: (
  router: Router,
  locale: LocaleCode,
  offset?: number,
  limit?: number,
  search?: string
) => Promise<AttributeWithOptions[]> = async (router, locale, offset, limit, search) => {
  const params: RouteParams = {locale};
  if (typeof offset !== 'undefined') {
    params.offset = offset;
  }
  if (typeof limit !== 'undefined') {
    params.limit = limit;
  }
  if (typeof search !== 'undefined') {
    params.search = search;
  }

  const url = router.generate('pim_table_attribute_get_select_attributes_with_options_count', params);
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  const json = await response.json();

  return json as AttributeWithOptions[];
};

const AttributeFetcher = {
  fetch: fetchAttribute,
  query: query,
  findAttributeWithOptions,
};

export {AttributeFetcher};
