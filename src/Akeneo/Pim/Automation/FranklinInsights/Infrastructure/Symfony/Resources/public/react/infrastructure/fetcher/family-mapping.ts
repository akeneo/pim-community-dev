import {getJSON} from 'jquery';

import {AttributesMapping} from '../../domain/model/attributes-mapping';
import {generateUrl} from '../service/url-generator';

export async function fetchByFamilyCode(familyCode: string): Promise<AttributesMapping> {
  const url = generateUrl('akeneo_franklin_insights_attributes_mapping_get', {identifier: familyCode});

  const response = (await getJSON(url)) as {mapping: AttributesMapping};

  return response.mapping;
}
