import AttributeFetcher from 'akeneoreferenceentity/domain/fetcher/attribute';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import hydrator from 'akeneoreferenceentity/application/hydrator/attribute';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

const routing = require('routing');

export class AttributeFetcherImplementation implements AttributeFetcher {
  async fetchAll(referenceEntityIdentifier: ReferenceEntityIdentifier): Promise<Attribute[]> {
    const backendAttributes = await getJSON(
      routing.generate('akeneo_reference_entities_attribute_index_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
      })
    ).catch(errorHandler);

    return hydrateAll<Attribute>(hydrator)(backendAttributes);
  }
}

export default new AttributeFetcherImplementation();
