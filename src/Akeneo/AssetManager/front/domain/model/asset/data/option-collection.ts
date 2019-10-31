import ValueData from 'akeneoassetmanager/domain/model/asset/data';
import OptionCode, {
  optioncodesAreEqual,
  isOptionCode,
} from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';
import {OptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';

class InvalidTypeError extends Error {}

type NormalizedOptionCollectionData = string[];

const isOptionCollectionData = (optionCodes: any) => optionCodes.every((optionCode: any) => isOptionCode(optionCode));

/**
 * Data representing an Option Collection, used for Asset Values for Attribute with type "Option Collection"
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionData extends ValueData {
  private constructor(private optionData: OptionCode[]) {
    super();

    if (!Array.isArray(optionData)) {
      throw new InvalidTypeError('OptionCollectionData expects an array of OptionCode as parameter to be created');
    }

    if (!isOptionCollectionData(optionData)) {
      throw new InvalidTypeError('OptionCollectionData expects an array of OptionCode as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(optionData: OptionCode[]): OptionCollectionData {
    return new OptionCollectionData(optionData);
  }

  public static createFromNormalized(
    optionData: NormalizedOptionCollectionData,
    attribute: OptionCollectionAttribute
  ): OptionCollectionData {
    if (null === optionData) {
      return new OptionCollectionData([]);
    }

    // We remove old options that are not present in the attribute anymore
    const options = attribute.options
      .filter((option: Option) => {
        return optionData.some((optionCode: OptionCode) => optioncodesAreEqual(optionCode, option.code));
      })
      .map((option: Option) => option.code);

    return new OptionCollectionData(options);
  }

  public count() {
    return this.optionData.length;
  }

  public isEmpty(): boolean {
    return 0 === this.optionData.length;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof OptionCollectionData &&
      this.optionData.length === data.optionData.length &&
      !this.optionData.some(
        (optionCode: OptionCode, index: number) => !optioncodesAreEqual(data.optionData[index], optionCode)
      )
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
