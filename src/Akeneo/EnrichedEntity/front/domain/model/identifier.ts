export default interface Identifier {
  equals: (identifier: Identifier) => boolean;
  stringValue: () => string;
};

class InvalidTypeError extends Error {}

export class IdentifierImplementation implements Identifier {
  private constructor(private identifier: string) {
    if ('string' !== typeof identifier) {
      throw new InvalidTypeError('Identifier expect a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(identifier: string): Identifier {
    return new IdentifierImplementation(identifier);
  }

  public equals(identifier: Identifier): boolean {
    return this.stringValue() === identifier.stringValue();
  }

  public stringValue(): string {
    return this.identifier;
  }
}

export const createIdentifier = IdentifierImplementation.create;
