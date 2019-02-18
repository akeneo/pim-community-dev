import {NormalizableAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import ReferenceEntityIdentifier, {
  NormalizedIdentifier as NormalizedReferenceEntityIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export type NormalizedRecordType = NormalizedReferenceEntityIdentifier | null;

export class InvalidArgumentError extends Error {}
export class InvalidCallError extends Error {}

export class RecordType implements NormalizableAdditionalProperty {
  private constructor(readonly recordType?: ReferenceEntityIdentifier) {
    if (undefined === recordType) {
      Object.freeze(this);

      return;
    }

    if (!(recordType instanceof ReferenceEntityIdentifier)) {
      throw new InvalidArgumentError('RecordType expects a ReferenceEntityIdentifier argument');
    }

    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return typeof value === 'string';
  }

  public static createFromNormalized(normalizedRecordType: NormalizedRecordType) {
    return null === normalizedRecordType
      ? new RecordType()
      : new RecordType(ReferenceEntityIdentifier.create(normalizedRecordType));
  }

  public normalize(): NormalizedRecordType {
    return undefined === this.recordType ? null : this.recordType.stringValue();
  }

  public static createFromString(recordType: string) {
    return '' === recordType ? RecordType.createFromNormalized(null) : RecordType.createFromNormalized(recordType);
  }

  public stringValue(): string {
    return undefined === this.recordType ? '' : this.recordType.stringValue();
  }

  public equals(recordType: RecordType) {
    return (
      (undefined === this.recordType && undefined === recordType.recordType) ||
      (undefined !== this.recordType &&
        undefined !== recordType.recordType &&
        this.recordType.equals(recordType.recordType))
    );
  }

  public getReferenceEntityIdentifier(): ReferenceEntityIdentifier {
    if (undefined === this.recordType) {
      throw new InvalidCallError('The reference entity identifier is undefined');
    }

    return this.recordType;
  }
}
