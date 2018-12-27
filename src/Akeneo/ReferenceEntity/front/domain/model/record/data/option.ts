import ValueData from 'akeneoreferenceentity/domain/model/record/data';
import OptionCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/type/option/option-code';
import {OptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';

class InvalidTypeError extends Error {}

type NormalizedOptionData = string | null;

/**
 * Data representing an Option, used for Record Values for Attribute with type "Option"
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionData extends ValueData {
  private constructor(private optionData: OptionCode | null) {
    super();
    Object.freeze(this);

    if (null === optionData) {
      return;
    }

    if (!(optionData instanceof OptionCode)) {
      throw new InvalidTypeError('OptionData expect an OptionCode as parameter to be created');
    }
  }

  public static create(optionData: OptionCode): OptionData {
    return new OptionData(optionData);
  }

  public static createFromNormalized(optionData: NormalizedOptionData, attribute: OptionAttribute): OptionData {
    // We have to handle the case where the previous value has an option not in the attribute anymore
    if (null === optionData || !attribute.hasOption(createCode(optionData))) {
      return new OptionData(null);
    }

    return new OptionData(createCode(optionData));
  }

  public isEmpty(): boolean {
    return null === this.optionData;
  }

  public getCode(): OptionCode {
    if (this.isEmpty()) {
      throw new Error('Cannot get the option code on an empty OptionData');
    }

    return this.optionData as OptionCode;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof OptionData &&
      ((null === this.optionData && null === data.optionData) ||
        (null !== this.optionData && null !== data.optionData && this.optionData.equals(data.optionData)))
    );
  }

  public stringValue(): string {
    return null !== this.optionData ? this.optionData.stringValue() : '';
  }

  public normalize(): string | null {
    return null !== this.optionData ? this.optionData.normalize() : null;
  }
}

export default OptionData;
export {NormalizedOptionData};
export const create = OptionData.create;
export const denormalize = OptionData.createFromNormalized;
