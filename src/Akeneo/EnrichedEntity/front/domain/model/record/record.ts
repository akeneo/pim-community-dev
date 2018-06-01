import Identifier from 'akeneoenrichedentity/domain/model/record/identifier';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';

export default interface Record {
  getIdentifier: () => Identifier;
  getEnrichedEntityIdentifier(): EnrichedEntityIdentifier;
  getLabel: (locale: string) => string;
  equals: (record: Record) => boolean;
};
class InvalidArgumentError extends Error {}

class RecordImplementation implements Record {
  private constructor(
    private identifier: Identifier,
    private enrichedEntityIdentifier: EnrichedEntityIdentifier,
    private labelCollection: LabelCollection
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Record expect a RecordIdentifier as first argument');
    }
    if (!(enrichedEntityIdentifier instanceof EnrichedEntityIdentifier)) {
      throw new InvalidArgumentError('Record expect an EnrichedEntityIdentifier as second argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Record expect a LabelCollection as third argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    labelCollection: LabelCollection
  ): Record {
    return new RecordImplementation(identifier, enrichedEntityIdentifier, labelCollection);
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getEnrichedEntityIdentifier(): EnrichedEntityIdentifier {
    return this.enrichedEntityIdentifier;
  }

  public getLabel(locale: string) {
    return this.labelCollection.hasLabel(locale)
      ? this.labelCollection.getLabel(locale)
      : `[${this.getIdentifier().stringValue()}]`;
  }

  public equals(record: Record): boolean {
    return (
      record.getIdentifier().equals(this.identifier) &&
      record.getEnrichedEntityIdentifier().equals(this.enrichedEntityIdentifier)
    );
  }
}

export const createRecord = RecordImplementation.create;
