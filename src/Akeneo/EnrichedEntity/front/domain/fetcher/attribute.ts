import Attribute from 'akeneoreferenceentity/domain/model/attribute/attribute';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export default interface Fetcher {
  fetchAll: (referenceEntityIdentifier: ReferenceEntityIdentifier) => Promise<Attribute[]>;
}
