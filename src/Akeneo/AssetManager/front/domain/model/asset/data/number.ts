import ValueData from 'akeneoassetmanager/domain/model/asset/data';

class InvalidTypeError extends Error {}

export type NormalizedNumberData = string | null;

class NumberData extends ValueData {
  private constructor(private numberData: string) {
    super();

    if ('string' !== typeof numberData) {
      throw new InvalidTypeError('NumberData expects a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(numberData: string): NumberData {
    return new NumberData(numberData);
  }

  public static createFromNormalized(numberData: NormalizedNumberData): NumberData {
    return new NumberData(null === numberData ? '' : numberData);
  }

  public isEmpty(): boolean {
    return 0 === this.numberData.length || '' === this.numberData;
  }

  public equals(data: ValueData): boolean {
    return data instanceof NumberData && this.numberData === data.numberData;
  }

  public stringValue(): string {
    return this.numberData;
  }

  public normalize(): string {
    return this.numberData;
  }
}

export default NumberData;
export const create = NumberData.create;
export const denormalize = NumberData.createFromNormalized;
