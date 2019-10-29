import {NormalizableAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/attribute';
import LabelCollection, {
  denormalizeLabelCollection,
  emptyLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import OptionCode, {denormalizeOptionCode} from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';

export type NormalizedOption = {
  code: OptionCode;
  labels: LabelCollection;
};

export class Option implements NormalizableAdditionalProperty {
  private constructor(readonly code: OptionCode, readonly labels: LabelCollection) {
    Object.freeze(this);
  }

  public static createFromNormalized(normalizedOption: NormalizedOption) {
    return new Option(
      denormalizeOptionCode(normalizedOption.code),
      denormalizeLabelCollection(normalizedOption.labels)
    );
  }

  public static create(optionCode: OptionCode, labels: LabelCollection) {
    return new Option(optionCode, labels);
  }

  public static createEmpty() {
    return new Option('', emptyLabelCollection());
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    return getLabelInCollection(this.labels, locale, fallbackOnCode, this.code);
  }

  public normalize(): NormalizedOption {
    return {
      code: this.code,
      labels: this.labels,
    };
  }
}
