import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import Product from 'akeneoreferenceentity/domain/model/product/product';

export default interface Fetcher {
  fetchLinkedProducts: (
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    recordCode: RecordCode,
    attributeCode: string
  ) => Promise<Product[]>;
}
