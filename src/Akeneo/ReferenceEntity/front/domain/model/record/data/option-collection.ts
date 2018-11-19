import Data from 'akeneoreferenceentity/domain/model/record/data';
import OptionCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/type/option/option-code';

class InvalidTypeError extends Error {}

type NormalizedOptionCollectionData = string[];

/**
 * Data representing an Option Collection, used for Record Values for Attribute with type "Option Collection"
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionData extends Data {
  private constructor(private optionData: OptionCode[]) {
    super();

    if (!Array.isArray(optionData)) {
      throw new InvalidTypeError('OptionCollectionData expect an array of OptionCode as parameter to be created');
    }

    optionData.forEach((option: OptionCode) => {
      if (!(option instanceof OptionCode)) {
        throw new InvalidTypeError('OptionCollectionData expect an array of OptionCode as parameter to be created');
      }
    });

    Object.freeze(this);
  }

  public static create(optionData: OptionCode[]): OptionCollectionData {
    return new OptionCollectionData(optionData);
  }

  public static createFromNormalized(optionData: NormalizedOptionCollectionData): OptionCollectionData {
    return new OptionCollectionData(
      null === optionData ? [] : optionData.map((optionCode: string) => createCode(optionCode))
    );
  }

  public contains(code: OptionCode) {
    return undefined !== this.optionData.find((optionCode: OptionCode) => optionCode.equals(code));
  }

  public count() {
    return this.optionData.length;
  }

  public isEmpty(): boolean {
    return 0 === this.optionData.length;
  }

  public equals(data: Data): boolean {
    return (
      data instanceof OptionCollectionData &&
      this.optionData.length === data.optionData.length &&
      !this.optionData.some((optionCode: OptionCode, index: number) => !data.optionData[index].equals(optionCode))
    );
  }

  public stringValue(): string {
    return this.normalize().join(', ');
  }

  public normalize(): string[] {
    return this.optionData.map((optionCode: OptionCode) => optionCode.normalize());
  }
}

export default OptionCollectionData;
export {NormalizedOptionCollectionData};
export const create = OptionCollectionData.create;
export const denormalize = OptionCollectionData.createFromNormalized;
