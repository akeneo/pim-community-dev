class InvalidTypeError extends Error {}

export interface NormalizedAttributeIdentifier {
  identifier: string;
  enrichedEntityIdentifier: string;
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

  public equals(identifier: Identifier): boolean {
    return (
      this.identifier === identifier.identifier && this.enrichedEntityIdentifier === identifier.enrichedEntityIdentifier
    );
  }

  public normalize(): NormalizedAttributeIdentifier {
    return {
      identifier: this.identifier,
      enrichedEntityIdentifier: this.enrichedEntityIdentifier,
    };
  }
}

export const createIdentifier = Identifier.create;
