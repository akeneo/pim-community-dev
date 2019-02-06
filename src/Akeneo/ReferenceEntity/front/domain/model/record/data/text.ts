import ValueData from 'akeneoreferenceentity/domain/model/record/data';

class InvalidTypeError extends Error {}

export type NormalizedTextData = string | null;

class TextData extends ValueData {
  private constructor(private textData: string) {
    super();

    if ('string' !== typeof textData) {
      throw new InvalidTypeError('TextData expects a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(textData: string): TextData {
    return new TextData(textData);
  }

  public static createFromNormalized(textData: NormalizedTextData): TextData {
    return new TextData(null === textData ? '' : textData);
  }

  public isEmpty(): boolean {
    return 0 === this.textData.length || '<p></p>\n' === this.textData;
  }

  public equals(data: ValueData): boolean {
    return data instanceof TextData && this.textData === data.textData;
  }

  public stringValue(): string {
    return this.textData;
  }

  public normalize(): string {
    return this.textData;
  }
}

export default TextData;
export const create = TextData.create;
export const denormalize = TextData.createFromNormalized;
