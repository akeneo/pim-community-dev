class InvalidTypeError extends Error {}

export type NormalizedAttributeIdentifier = string;

export default class AttributeIdentifier {
  private constructor(readonly identifier: string) {
    if ('string' !== typeof identifier) {
      throw new InvalidTypeError('AttributeIdentifier expects a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(identifier: string): AttributeIdentifier {
    return new AttributeIdentifier(identifier);
  }

  public static createFromNormalized(identifier: NormalizedAttributeIdentifier): AttributeIdentifier {
    return new AttributeIdentifier(identifier);
  }

  public equals(identifier: AttributeIdentifier): boolean {
    return this.identifier === identifier.identifier;
  }

  public normalize(): NormalizedAttributeIdentifier {
    return this.identifier;
  }

  public stringValue(): string {
    return this.identifier;
  }
}

export const createIdentifier = AttributeIdentifier.create;
export const denormalizeIdentifier = AttributeIdentifier.createFromNormalized;
