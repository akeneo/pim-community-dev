import File, {NormalizedFile} from 'akeneoreferenceentity/domain/model/file';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {NormalizedLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import Identifier, {NormalizedRecordIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';
import ValueCollection from 'akeneoreferenceentity/domain/model/record/value-collection';
import {NormalizedValue, NormalizedMinimalValue} from 'akeneoreferenceentity/domain/model/record/value';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import Completeness, {NormalizedCompleteness} from 'akeneoreferenceentity/domain/model/record/completeness';

interface CommonNormalizedRecord {
  identifier: NormalizedRecordIdentifier;
  reference_entity_identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export interface NormalizedRecord extends CommonNormalizedRecord {
  values: NormalizedValue[];
}

export interface NormalizedItemRecord extends CommonNormalizedRecord {
  values: NormalizedValue[];
  completeness: NormalizedCompleteness;
}

export interface NormalizedMinimalRecord extends CommonNormalizedRecord {
  values: NormalizedMinimalValue[];
}

export enum NormalizeFormat {
  Standard,
  Minimal,
}

export default interface Record {
  getIdentifier: () => Identifier;
  getCode: () => RecordCode;
  getReferenceEntityIdentifier: () => ReferenceEntityIdentifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  getValueCollection: () => ValueCollection;
  equals: (record: Record) => boolean;
  normalize: () => NormalizedRecord;
  normalizeMinimal: () => NormalizedMinimalRecord;
  getCompleteness: (channel: ChannelReference, locale: LocaleReference) => Completeness;
}

class InvalidArgumentError extends Error {}

class RecordImplementation implements Record {
  private constructor(
    private identifier: Identifier,
    private referenceEntityIdentifier: ReferenceEntityIdentifier,
    private code: RecordCode,
    private labelCollection: LabelCollection,
    private image: File,
    private valueCollection: ValueCollection
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Record expects a RecordIdentifier as identifier argument');
    }
    if (!(referenceEntityIdentifier instanceof ReferenceEntityIdentifier)) {
      throw new InvalidArgumentError(
        'Record expects an ReferenceEntityIdentifier as referenceEntityIdentifier argument'
      );
    }
    if (!(code instanceof RecordCode)) {
      throw new InvalidArgumentError('Record expects a RecordCode as code argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Record expects a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('Record expects a File as image argument');
    }
    if (!(valueCollection instanceof ValueCollection)) {
      throw new InvalidArgumentError('Record expects a ValueCollection as valueCollection argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    recordCode: RecordCode,
    labelCollection: LabelCollection,
    image: File,
    valueCollection: ValueCollection
  ): Record {
    return new RecordImplementation(
      identifier,
      referenceEntityIdentifier,
      recordCode,
      labelCollection,
      image,
      valueCollection
    );
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getReferenceEntityIdentifier(): ReferenceEntityIdentifier {
    return this.referenceEntityIdentifier;
  }

  public getCode(): RecordCode {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getImage(): File {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getValueCollection(): ValueCollection {
    return this.valueCollection;
  }

  public equals(record: Record): boolean {
    return record.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedRecord {
    return {
      identifier: this.getIdentifier().normalize(),
      reference_entity_identifier: this.getReferenceEntityIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      values: this.valueCollection.normalize(),
    };
  }

  public normalizeMinimal(): NormalizedMinimalRecord {
    return {
      identifier: this.getIdentifier().normalize(),
      reference_entity_identifier: this.getReferenceEntityIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      values: this.valueCollection.normalizeMinimal(),
    };
  }

  public getCompleteness(channel: ChannelReference, locale: LocaleReference): Completeness {
    const values = this.getValueCollection().getValuesForChannelAndLocale(channel, locale);

    return Completeness.createFromValues(values);
  }
}

export const createRecord = RecordImplementation.create;
