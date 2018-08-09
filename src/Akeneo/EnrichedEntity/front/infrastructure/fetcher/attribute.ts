import AttributeFetcher from 'akeneoenrichedentity/domain/fetcher/attribute';
import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import hydrator from 'akeneoenrichedentity/application/hydrator/attribute';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
// import {getJSON} from 'akeneoenrichedentity/tools/fetch';

// const routing = require('routing');

export class AttributeFetcherImplementation implements AttributeFetcher {
  constructor(private hydrator: (backendAttribute: any) => Attribute) {
    Object.freeze(this);
  }

  async fetchAll(enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Attribute[]> {
    console.log(enrichedEntityIdentifier);
    // const backendAttributes = await getJSON(
    //   routing.generate('akeneo_enriched_entities_attribute_index_rest', {enrichedEntityIdentifier})
    // );
    const backendAttributes = await Promise.resolve([
      {
        identifier: {
          identifier: 'description',
          enriched_entity_identifier: 'designer',
        },
        enriched_entity_identifier: 'designer',
        code: 'description',
        required: true,
        order: 0,
        value_per_locale: true,
        value_per_channel: false,
        type: 'text',
        labels: {
          en_US: 'Description',
        },
        maxLenth: 255,
      },
      {
        identifier: {
          identifier: 'side_view',
          enriched_entity_identifier: 'designer',
        },
        enriched_entity_identifier: 'designer',
        code: 'side_view',
        required: false,
        order: 1,
        value_per_locale: true,
        value_per_channel: false,
        type: 'image',
        labels: {
          en_US: 'Side view',
        },
      },
      {
        identifier: {
          identifier: 'model',
          enriched_entity_identifier: 'designer',
        },
        enriched_entity_identifier: 'designer',
        code: 'model',
        required: true,
        order: 0,
        value_per_locale: true,
        value_per_channel: false,
        type: 'text',
        labels: {
          en_US: 'Model',
        },
        maxLenth: 255,
      },
    ]);

    return hydrateAll<Attribute>(this.hydrator)(backendAttributes);
  }
}

export default new AttributeFetcherImplementation(hydrator);
