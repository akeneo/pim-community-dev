import Data from 'akeneoreferenceentity/domain/model/record/data';

class InvalidTypeError extends Error {}

type NormalizedOptionCollectionData = string[];

/**
 * Data representing an Option Collection, used for Record Values for Attribute with type "Option Collection"
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionData extends Data {
  private constructor(private optionData: string[]) {
    super();

    if (!Array.isArray(optionData)) {
      throw new InvalidTypeError('OptionCollectionData expect an array of string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(optionData: string[]): OptionCollectionData {
    return new OptionCollectionData(optionData);
  }

  public static createFromNormalized(optionData: NormalizedOptionCollectionData): OptionCollectionData {
    return new OptionCollectionData(null === optionData ? [] : optionData);
  }

  public isEmpty(): boolean {
    return 0 === this.optionData.length;
  }

  public equals(data: Data): boolean {
    return data instanceof OptionCollectionData && this.optionData === data.optionData;
  }

  public stringValue(): string {
    return this.optionData.join(', ');
  }

  public normalize(): string[] {
    return this.optionData;
  }
}

export default OptionCollectionData;
export {NormalizedOptionCollectionData};
export const create = OptionCollectionData.create;
export const denormalize = OptionCollectionData.createFromNormalized;
