import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export default interface Remover<EnrichedEntityIdentifier, Identifier> {
  remove: (
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    identifier: Identifier
  ) => Promise<ValidationError[] | null>;
}
