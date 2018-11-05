import {NormalizableAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import LabelCollection, {
  createLabelCollection,
  NormalizedLabelCollection
} from 'akeneoreferenceentity/domain/model/label-collection';
import OptionCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/type/option/option-code';

export type NormalizedOption = {
  code: NormalizedOption,
  labelCollection: NormalizedLabelCollection
}

export class Option implements NormalizableAdditionalProperty {
  private constructor(readonly option: OptionCode, readonly labelCollection: LabelCollection) {
    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOption: NormalizedOption) {
    return new Option(
      createCode(normalizedOption.code),
      createLabelCollection(normalizedOption.labelCollection)
    );
  }

  public normalize(): NormalizedOption {
    return {
      'code': this.option.normalize(),
      'labelCollection': this.labelCollection.normalize()
    }
  }

  public static create(optionCode: OptionCode, labelCollection: LabelCollection) {
    return new Option(optionCode, labelCollection);
  }
}
