import ValueData from 'akeneoreferenceentity/domain/model/record/data';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';

class InvalidTypeError extends Error {}

export type NormalizedRecordCollectionData = string[] | null;

class RecordCollectionData extends ValueData {
  private constructor(readonly recordCollectionData: RecordCode[]) {
    super();
    Object.freeze(this);

    if (!Array.isArray(recordCollectionData)) {
      throw new InvalidTypeError('RecordCollectionData expects an array of RecordCode as parameter to be created');
    }

    recordCollectionData.forEach((recordCode: RecordCode) => {
      if (!(recordCode instanceof RecordCode)) {
        throw new InvalidTypeError('RecordCollectionData expects an array of RecordCode as parameter to be created');
      }
    });
  }

  public static create(recordCollectionData: RecordCode[]): RecordCollectionData {
    return new RecordCollectionData(recordCollectionData);
  }

  public static createFromNormalized(
    normalizedRecordCollectionData: NormalizedRecordCollectionData
  ): RecordCollectionData {
    return new RecordCollectionData(
      Array.isArray(normalizedRecordCollectionData)
        ? normalizedRecordCollectionData.map((recordCode: string) => RecordCode.create(recordCode))
        : []
    );
  }

  public isEmpty(): boolean {
    return 0 === this.recordCollectionData.length;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof RecordCollectionData &&
      this.recordCollectionData.length === data.recordCollectionData.length &&
      !this.recordCollectionData.some((recordCode: RecordCode, index: number) => {
        return !recordCode.equals(data.recordCollectionData[index]);
      })
    );
  }

  public normalize(): NormalizedRecordCollectionData {
    return this.recordCollectionData.map((recordCode: RecordCode) => recordCode.stringValue());
  }
}

export default RecordCollectionData;
export const create = RecordCollectionData.create;
export const denormalize = RecordCollectionData.createFromNormalized;
