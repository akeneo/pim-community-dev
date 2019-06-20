import ValueData from 'akeneoreferenceentity/domain/model/record/data';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';

class InvalidTypeError extends Error {}

export type NormalizedRecordData = string | null;

class RecordData extends ValueData {
  private constructor(readonly recordData: RecordCode | null) {
    super();
    Object.freeze(this);

    if (null === recordData) {
      return;
    }

    if (!(recordData instanceof RecordCode)) {
      throw new InvalidTypeError('RecordData expects a RecordCode as parameter to be created');
    }
  }

  public static create(recordData: RecordCode): RecordData {
    return new RecordData(recordData);
  }

  public static createFromNormalized(normalizedRecordData: NormalizedRecordData): RecordData {
    return new RecordData(null === normalizedRecordData ? null : RecordCode.create(normalizedRecordData));
  }

  public isEmpty(): boolean {
    return null === this.recordData;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof RecordData &&
      ((this.isEmpty() && data.isEmpty()) ||
        (data.recordData !== null && this.recordData !== null && this.recordData.equals(data.recordData)))
    );
  }

  public normalize(): NormalizedRecordData {
    return null === this.recordData ? null : this.recordData.stringValue();
  }
}

export default RecordData;
export const create = RecordData.create;
export const denormalize = RecordData.createFromNormalized;
