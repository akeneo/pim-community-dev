import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';

export default interface Remover<ReferenceEntityIdentifier, Identifier> {
  remove: (
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    identifier: Identifier
  ) => Promise<ValidationError[] | null>;

  removeAll: (referenceEntityIdentifier: ReferenceEntityIdentifier) => Promise<ValidationError[] | null>;
}
