import ProductFetcher from 'akeneoreferenceentity/domain/fetcher/product';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import hydrator from 'akeneoreferenceentity/application/hydrator/product';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import Product from 'akeneoreferenceentity/domain/model/product/product';

const routing = require('routing');

export class ProductFetcherImplementation implements ProductFetcher {
  async fetchLinkedProducts(
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    recordCode: RecordCode,
    attributeCode: string
  ): Promise<Product[]> {
    const backendProducts = await getJSON(
      routing.generate('akeneo_reference_entities_product_get_linked_product', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        recordCode: recordCode.stringValue(),
        attributeCode: attributeCode,
      })
    ).catch(errorHandler);

    return hydrateAll<Product>(hydrator)(backendProducts);
  }
}

export default new ProductFetcherImplementation();
