import Identifier, {
  NormalizedRecordIdentifier,
  createIdentifier,
} from 'akeneoenrichedentity/domain/model/record/identifier';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {RawLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';

interface NormalizedRecord {
  identifier: NormalizedRecordIdentifier;
  enrichedEntityIdentifier: string;
  code: string;
  labels: RawLabelCollection;
}

export default interface Record {
  getIdentifier: () => Identifier;
  getCode: () => RecordCode;
  getEnrichedEntityIdentifier: () => EnrichedEntityIdentifier;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  equals: (record: Record) => boolean;
  normalize: () => NormalizedRecord;
}

class InvalidArgumentError extends Error {}

class RecordImplementation implements Record {
  private constructor(
    private identifier: Identifier,
    private enrichedEntityIdentifier: EnrichedEntityIdentifier,
    private code: RecordCode,
    private labelCollection: LabelCollection
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Record expect a RecordIdentifier as first argument');
    }
    if (!(enrichedEntityIdentifier instanceof EnrichedEntityIdentifier)) {
      throw new InvalidArgumentError('Record expect an EnrichedEntityIdentifier as second argument');
    }
    if (!(code instanceof RecordCode)) {
      throw new InvalidArgumentError('Record expect a RecordCode as third argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Record expect a LabelCollection as fourth argument');
    }
    if (!createIdentifier(enrichedEntityIdentifier.stringValue(), code.stringValue()).equals(identifier)) {
      throw new InvalidArgumentError(
        'Record expect an identifier complient to the given enrichedEntityIdentifier and code'
      );
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    recordCode: RecordCode,
    labelCollection: LabelCollection
  ): Record {
    return new RecordImplementation(identifier, enrichedEntityIdentifier, recordCode, labelCollection);
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
      labels: this.getLabelCollection().getLabels(),
    };
  }
}

export const createRecord = RecordImplementation.create;
