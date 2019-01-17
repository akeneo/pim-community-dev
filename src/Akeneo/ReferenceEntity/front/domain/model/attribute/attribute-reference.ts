class InvalidTypeError extends Error {}

export type NormalizedAttributeReference = string | null;

export default class AttributeReference {
  private constructor(readonly identifier: string | null) {
    if (!('string' === typeof identifier || null === identifier)) {
      throw new InvalidTypeError('AttributeReference expect a string or null as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(identifier: string | null): AttributeReference {
    return new AttributeReference(identifier);
  }

  public static createFromNormalized(identifier: NormalizedAttributeReference): AttributeReference {
    return new AttributeReference(identifier);
  }

  public equals(identifier: AttributeReference): boolean {
    return this.identifier === identifier.identifier;
  }

  public normalize(): NormalizedAttributeReference {
    return this.identifier;
  }

  public stringValue(): string | null {
    return this.identifier;
  }
}

export const createAttributeReference = AttributeReference.create;
export const denormalizeAttributeReference = AttributeReference.createFromNormalized;
