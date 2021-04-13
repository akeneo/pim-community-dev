import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';

export default interface Remover<ReferenceEntityIdentifier, Identifier> {
  remove: (
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    identifier: Identifier
  ) => Promise<ValidationError[] | null>;

  removeFromQuery: (referenceEntityIdentifier: ReferenceEntityIdentifier, query: Query) => Promise<Response>;
}
