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
import Image from 'akeneoenrichedentity/domain/model/image';

export interface NormalizedRecord {
  identifier: NormalizedRecordIdentifier;
  enrichedEntityIdentifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: Image | null;
}

export default interface Record {
  getIdentifier: () => Identifier;
  getCode: () => RecordCode;
  getEnrichedEntityIdentifier: () => EnrichedEntityIdentifier;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => Image | null;
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
    private image: Image | null
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Record expect a RecordIdentifier as argument');
    }
    if (!(enrichedEntityIdentifier instanceof EnrichedEntityIdentifier)) {
      throw new InvalidArgumentError('Record expect an EnrichedEntityIdentifier as argument');
    }
    if (!(code instanceof RecordCode)) {
      throw new InvalidArgumentError('Record expect a RecordCode as argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Record expect a LabelCollection as argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    recordCode: RecordCode,
    labelCollection: LabelCollection,
    image: Image | null
  ): Record {
    return new RecordImplementation(identifier, enrichedEntityIdentifier, recordCode, labelCollection, image);
  }

  public static createFromNormalized(normalizedRecord: NormalizedRecord): Record {
    const identifier = createIdentifier(normalizedRecord.identifier);
    const code = createCode(normalizedRecord.identifier);
    const enrichedEntityIdentifier = createEnrichedEntityIdentifier(normalizedRecord.enrichedEntityIdentifier);
    const labelCollection = createLabelCollection(normalizedRecord.labels);

    return RecordImplementation.create(
      identifier,
      enrichedEntityIdentifier,
      code,
      labelCollection,
      normalizedRecord.image
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

  public getLabel(locale: string) {
    return this.labelCollection.hasLabel(locale)
      ? this.labelCollection.getLabel(locale)
      : `[${this.getCode().stringValue()}]`;
  }

  public getImage(): Image | null {
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
      enrichedEntityIdentifier: this.getEnrichedEntityIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage(),
    };
  }
}

export const createRecord = RecordImplementation.create;
export const denormalizeRecord = RecordImplementation.createFromNormalized;
