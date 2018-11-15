import Data from 'akeneoreferenceentity/domain/model/record/data';

class InvalidTypeError extends Error {}

type NormalizedOptionData = string | null;

/**
 * Data representing an Option, used for Record Values for Attribute with type "Option" and "Option Collection"
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionData extends Data {
  private constructor(private optionData: string) {
    super();

    if ('string' !== typeof optionData) {
      throw new InvalidTypeError('OptionData expect a string as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(optionData: string): OptionData {
    return new OptionData(optionData);
  }

  public static createFromNormalized(optionData: NormalizedOptionData): OptionData {
    return new OptionData(null === optionData ? '' : optionData);
  }

  public isEmpty(): boolean {
    return 0 === this.optionData.length;
  }

  public equals(data: Data): boolean {
    return data instanceof OptionData && this.optionData === data.optionData;
  }

  public stringValue(): string {
    return this.optionData;
  }

  public normalize(): string {
    return this.optionData;
  }
}

export default OptionData;
export {NormalizedOptionData};
export const create = OptionData.create;
export const denormalize = OptionData.createFromNormalized;
