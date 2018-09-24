import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoenrichedentity/domain/model/label-collection';
import RecordCode, {createCode} from 'akeneoenrichedentity/domain/model/record/code';
import Identifier, {
  NormalizedRecordIdentifier,
  createIdentifier,
} from 'akeneoenrichedentity/domain/model/record/identifier';
import File, {NormalizedFile, denormalizeFile} from 'akeneoenrichedentity/domain/model/file';

export interface NormalizedRecord {
  identifier: NormalizedRecordIdentifier;
  enriched_entity_identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export default interface Record {
  getIdentifier: () => Identifier;
  getCode: () => RecordCode;
  getEnrichedEntityIdentifier: () => EnrichedEntityIdentifier;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  equals: (record: Record) => boolean;
  normalize: () => NormalizedRecord;
}

class InvalidArgumentError extends Error {}

class RecordImplementation implements Record {
  private constructor(
    private identifier: Identifier,
    private enrichedEntityIdentifier: EnrichedEntityIdentifier,
    private code: RecordCode,
    private labelCollection: LabelCollection,
    private image: File
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

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    recordCode: RecordCode,
    labelCollection: LabelCollection,
    image: File
  ): Record {
    return new RecordImplementation(identifier, enrichedEntityIdentifier, recordCode, labelCollection, image);
  }

  public static createFromNormalized(normalizedRecord: NormalizedRecord): Record {
    const identifier = createIdentifier(normalizedRecord.identifier);
    const code = createCode(normalizedRecord.code);
    const enrichedEntityIdentifier = createEnrichedEntityIdentifier(normalizedRecord.enriched_entity_identifier);
    const labelCollection = createLabelCollection(normalizedRecord.labels);
    const image = denormalizeFile(normalizedRecord.image);

    return RecordImplementation.create(identifier, enrichedEntityIdentifier, code, labelCollection, image);
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

  public getLabel(locale: string) {
    return this.labelCollection.hasLabel(locale)
      ? this.labelCollection.getLabel(locale)
      : `[${this.getCode().stringValue()}]`;
  }

  public getImage(): File {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
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
    };
  }
}

export const createRecord = RecordImplementation.create;
export const denormalizeRecord = RecordImplementation.createFromNormalized;
