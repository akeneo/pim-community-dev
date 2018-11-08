import {NormalizableAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import LabelCollection, {
  createLabelCollection,
  NormalizedLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import OptionCode, {createCode} from 'akeneoreferenceentity/domain/model/attribute/type/option/option-code';

export type NormalizedOption = {
  code: string;
  labels: NormalizedLabelCollection;
};

export class Option implements NormalizableAdditionalProperty {
  private constructor(readonly option: OptionCode, readonly labels: LabelCollection) {
    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOption: NormalizedOption) {
    return new Option(createCode(normalizedOption.code), createLabelCollection(normalizedOption.labels));
  }

  public static create(optionCode: OptionCode, labels: LabelCollection) {
    return new Option(optionCode, labels);
  }

  public normalize(): NormalizedOption {
    return {
      code: this.option.normalize(),
      labels: this.labels.normalize(),
    };
  }
}
