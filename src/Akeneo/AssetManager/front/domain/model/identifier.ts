class InvalidTypeError extends Error {}

export default class Identifier {
  private constructor(private identifier: string) {
    if ('string' !== typeof identifier) {
      throw new InvalidTypeError('Identifier expects a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(identifier: string): Identifier {
    return new Identifier(identifier);
  }

  public equals(identifier: Identifier): boolean {
    return this.stringValue() === identifier.stringValue();
  }

  public stringValue(): string {
    return this.identifier;
  }
}

export type NormalizedIdentifier = string;

export const createIdentifier = Identifier.create;
