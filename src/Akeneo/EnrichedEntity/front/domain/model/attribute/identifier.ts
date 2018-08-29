class InvalidTypeError extends Error {}

export interface NormalizedAttributeIdentifier {
  identifier: string;
  enriched_entity_identifier: string;
}

export default class Identifier {
  private constructor(readonly enrichedEntityIdentifier: string, readonly identifier: string) {
    if ('string' !== typeof enrichedEntityIdentifier) {
      throw new InvalidTypeError('AttributeIdentifier expect a string as first parameter to be created');
    }
    if ('string' !== typeof identifier) {
      throw new InvalidTypeError('AttributeIdentifier expect a string as second parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(enrichedEntityIdentifier: string, identifier: string): Identifier {
    return new Identifier(enrichedEntityIdentifier, identifier);
  }

  public static createFromNormalized({
    enriched_entity_identifier,
    identifier,
  }: NormalizedAttributeIdentifier): Identifier {
    return new Identifier(enriched_entity_identifier, identifier);
  }

  public equals(identifier: Identifier): boolean {
    return (
      this.identifier === identifier.identifier && this.enrichedEntityIdentifier === identifier.enrichedEntityIdentifier
    );
  }

  public normalize(): NormalizedAttributeIdentifier {
    return {
      identifier: this.identifier,
      enriched_entity_identifier: this.enrichedEntityIdentifier,
    };
  }
}

export const createIdentifier = Identifier.create;
export const denormalizeIdentifier = Identifier.createFromNormalized;
