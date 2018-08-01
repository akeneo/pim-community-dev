class InvalidTypeError extends Error {}

export default class Code {
  private constructor(private code: string) {
    if ('string' !== typeof code) {
      throw new InvalidTypeError('Code expect a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(code: string): Code {
    return new Code(code);
  }

  public equals(code: Code): boolean {
    return this.stringValue() === code.stringValue();
  }

  public stringValue(): string {
    return this.code;
  }
}

export const createCode = Code.create;
