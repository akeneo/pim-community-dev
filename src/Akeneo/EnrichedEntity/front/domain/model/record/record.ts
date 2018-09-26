import File, {NormalizedFile} from 'akeneoenrichedentity/domain/model/file';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {NormalizedLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';
import Identifier, {NormalizedRecordIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import ValueCollection from 'akeneoenrichedentity/domain/model/record/value-collection';
import {NormalizedValue, NormalizedMinimalValue} from 'akeneoenrichedentity/domain/model/record/value';

interface CommonNormalizedRecord {
  identifier: NormalizedRecordIdentifier;
  enriched_entity_identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export interface NormalizedRecord extends CommonNormalizedRecord {
  values: NormalizedValue[];
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
  getEnrichedEntityIdentifier: () => EnrichedEntityIdentifier;
  getLabel: (locale: string, defaultValue?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  getValueCollection: () => ValueCollection;
  equals: (record: Record) => boolean;
  normalize: () => NormalizedRecord;
  normalizeMinimal: () => NormalizedMinimalRecord;
}

class InvalidArgumentError extends Error {}

class RecordImplementation implements Record {
  private constructor(
    private identifier: Identifier,
    private enrichedEntityIdentifier: EnrichedEntityIdentifier,
    private code: RecordCode,
    private labelCollection: LabelCollection,
    private image: File,
    private valueCollection: ValueCollection
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Record expect a RecordIdentifier as identifier argument');
    }
    if (!(enrichedEntityIdentifier instanceof EnrichedEntityIdentifier)) {
      throw new InvalidArgumentError('Record expect an EnrichedEntityIdentifier as enrichedEntityIdentifier argument');
    }
    if (!(code instanceof RecordCode)) {
      throw new InvalidArgumentError('Record expect a RecordCode as code argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Record expect a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('Record expect a File as image argument');
    }
    if (!(valueCollection instanceof ValueCollection)) {
      throw new InvalidArgumentError('Record expect a ValueCollection as valueCollection argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    recordCode: RecordCode,
    labelCollection: LabelCollection,
    image: File,
    valueCollection: ValueCollection
  ): Record {
    return new RecordImplementation(
      identifier,
      enrichedEntityIdentifier,
      recordCode,
      labelCollection,
      image,
      valueCollection
    );
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getEnrichedEntityIdentifier(): EnrichedEntityIdentifier {
    return this.enrichedEntityIdentifier;
  }

  public getCode(): RecordCode {
    return this.code;
  }

  public getLabel(locale: string, defaultValue: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return defaultValue ? `[${this.getCode().stringValue()}]` : '';
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
      enriched_entity_identifier: this.getEnrichedEntityIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      values: this.valueCollection.normalize(),
    };
  }

  public normalizeMinimal(): NormalizedMinimalRecord {
    return {
      identifier: this.getIdentifier().normalize(),
      enriched_entity_identifier: this.getEnrichedEntityIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      values: this.valueCollection.normalizeMinimal(),
    };
  }
}

export const createRecord = RecordImplementation.create;
