import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import Product from 'akeneoreferenceentity/domain/model/product/product';
import AttributeCode from 'akeneoreferenceentity/domain/model/product/attribute/code';

export default interface Fetcher {
  fetchLinkedProducts: (
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    recordCode: RecordCode,
    attributeCode: AttributeCode
  ) => Promise<Product[]>;
}
