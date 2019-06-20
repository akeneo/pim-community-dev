import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export default interface Fetcher {
  fetchAll: (referenceEntityIdentifier: ReferenceEntityIdentifier) => Promise<Attribute[]>;
}
