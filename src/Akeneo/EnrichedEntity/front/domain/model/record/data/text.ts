import Data from 'akeneoenrichedentity/domain/model/record/data';

class InvalidTypeError extends Error {}

export type NormalizedTextData = string;

export default class TextData extends Data {
  private constructor(private textData: string) {
    super();

    if ('string' !== typeof textData) {
      throw new InvalidTypeError('TextData expect a string or null as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(textData: string): TextData {
    return new TextData(textData);
  }

  public static createFromNormalized(textData: string): TextData {
    return new TextData(textData);
  }

  public stringValue(): string {
    return this.textData;
  }

  public normalize(): string {
    return this.textData;
  }
}

export const create = TextData.create;
export const denormalize = TextData.createFromNormalized;
