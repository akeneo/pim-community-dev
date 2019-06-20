import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import Code, {NormalizedCode, createCode} from 'akeneoreferenceentity/domain/model/code';

export interface NormalizedReferenceEntityCreation {
  code: NormalizedCode;
  labels: NormalizedLabelCollection;
}

export default interface ReferenceEntityCreation {
  getCode: () => Code;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  equals: (referenceEntityCreation: ReferenceEntityCreation) => boolean;
  normalize: () => NormalizedReferenceEntityCreation;
}
class InvalidArgumentError extends Error {}

class ReferenceEntityCreationImplementation implements ReferenceEntityCreation {
  private constructor(private code: Code, private labelCollection: LabelCollection) {
    if (!(code instanceof Code)) {
      throw new InvalidArgumentError('ReferenceEntityCreation expects a Code as code argument');
    }

    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('ReferenceEntityCreation expects a LabelCollection as labelCollection argument');
    }

    Object.freeze(this);
  }

  public static create(code: Code, labelCollection: LabelCollection): ReferenceEntityCreation {
    return new ReferenceEntityCreationImplementation(code, labelCollection);
  }

  public static createEmpty(): ReferenceEntityCreation {
    return new ReferenceEntityCreationImplementation(createCode(''), createLabelCollection({}));
  }

  public static createFromNormalized(
    normalizedReferenceEntity: NormalizedReferenceEntityCreation
  ): ReferenceEntityCreation {
    const code = createCode(normalizedReferenceEntity.code);
    const labelCollection = createLabelCollection(normalizedReferenceEntity.labels);

    return ReferenceEntityCreationImplementation.create(code, labelCollection);
  }

  public getCode(): Code {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(referenceEntityCreation: ReferenceEntityCreation): boolean {
    return referenceEntityCreation.getCode().equals(this.code);
  }

  public normalize(): NormalizedReferenceEntityCreation {
    return {
      code: this.getCode().stringValue(),
      labels: this.getLabelCollection().normalize(),
    };
  }
}

export const createReferenceEntityCreation = ReferenceEntityCreationImplementation.create;
export const createEmptyReferenceEntityCreation = ReferenceEntityCreationImplementation.createEmpty;
export const denormalizeReferenceEntityCreation = ReferenceEntityCreationImplementation.createFromNormalized;
