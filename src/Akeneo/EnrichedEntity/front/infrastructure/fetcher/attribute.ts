import AttributeFetcher from 'akeneoenrichedentity/domain/fetcher/attribute';
import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import hydrator from 'akeneoenrichedentity/application/hydrator/attribute';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

const routing = require('routing');

export class AttributeFetcherImplementation implements AttributeFetcher {
  constructor(private hydrator: (backendAttribute: any) => Attribute) {
    Object.freeze(this);
  }

  async fetchAll(enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Attribute[]> {
    const backendAttributes = await getJSON(
      routing.generate('akeneo_enriched_entities_attribute_index_rest', {
        enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
      })
    ).catch(errorHandler);

    return hydrateAll<Attribute>(this.hydrator)(backendAttributes);
  }
}

export default new AttributeFetcherImplementation(hydrator);
