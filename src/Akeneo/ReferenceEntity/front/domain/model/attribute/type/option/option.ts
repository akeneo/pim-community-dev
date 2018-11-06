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
  private constructor(readonly code: OptionCode, readonly labels: LabelCollection) {
    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOption: NormalizedOption) {
    return new Option(createCode(normalizedOption.code), createLabelCollection(normalizedOption.labels));
  }

  public static create(optionCode: OptionCode, labels: LabelCollection) {
    return new Option(optionCode, labels);
  }

  public static createEmpty() {
    return new Option(OptionCode.create(''), createLabelCollection({}));
  }

  public getLabel(locale: string, defaultValue: boolean = true) {
    if (!this.labels.hasLabel(locale)) {
      return defaultValue ? `[${this.code.stringValue()}]` : '';
    }

    return this.labels.getLabel(locale);
  }

  public normalize(): NormalizedOption {
    return {
      code: this.code.normalize(),
      labels: this.labels.normalize(),
    };
  }
}
