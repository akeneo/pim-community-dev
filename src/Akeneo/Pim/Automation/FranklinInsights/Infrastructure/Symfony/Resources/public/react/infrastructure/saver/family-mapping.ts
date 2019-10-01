import {ajax} from 'jquery';
import {AttributesMapping} from '../../domain/model/attributes-mapping';

const Routing = require('routing');

export async function saveFamilyMapping(familyCode: string, mapping: AttributesMapping): Promise<void> {
  const url = Routing.generate('akeneo_franklin_insights_attributes_mapping_update', {identifier: familyCode});

  const request = {mapping};

  return await ajax({
    url,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(request)
  });
}
