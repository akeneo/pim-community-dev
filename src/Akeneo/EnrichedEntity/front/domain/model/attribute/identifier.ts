class InvalidTypeError extends Error {}

export type NormalizedAttributeIdentifier = string;

export default class Identifier {
  private constructor(readonly identifier: string) {
    if ('string' !== typeof identifier) {
      throw new InvalidTypeError('AttributeIdentifier expect a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(identifier: string): Identifier {
    return new Identifier(identifier);
  }

  public static createFromNormalized(identifier: NormalizedAttributeIdentifier): Identifier {
    return new Identifier(identifier);
  }

  public equals(identifier: Identifier): boolean {
    return this.identifier === identifier.identifier;
  }

  public normalize(): NormalizedAttributeIdentifier {
    return this.identifier;
  }

  public stringValue(): string {
    return this.identifier;
  }
}

export const createIdentifier = Identifier.create;
export const denormalizeIdentifier = Identifier.createFromNormalized;
