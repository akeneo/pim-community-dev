import Data from 'akeneoreferenceentity/domain/model/record/data';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';

class InvalidTypeError extends Error {}

export type NormalizedRecordCollectionData = string[] | null;

class RecordCollectionData extends Data {
  private constructor(readonly recordData: RecordCode[]) {
    super();
    Object.freeze(this);

    if (!Array.isArray(recordData)) {
      throw new InvalidTypeError('RecordCollectionData expect an array of RecordCode as parameter to be created');
    }

    recordData.forEach((recordCode: RecordCode) => {
      if (!(recordCode instanceof RecordCode)) {
        throw new InvalidTypeError('RecordCollectionData expect an array of RecordCode as parameter to be created');
      }
    });
  }

  public static create(recordData: RecordCode[]): RecordCollectionData {
    return new RecordCollectionData(recordData);
  }

  public static createFromNormalized(
    normalizedRecordCollectionData: NormalizedRecordCollectionData
  ): RecordCollectionData {
    return null === normalizedRecordCollectionData
      ? new RecordCollectionData([])
      : new RecordCollectionData(
          normalizedRecordCollectionData.map((recordCode: string) => RecordCode.create(recordCode))
        );
  }

  public isEmpty(): boolean {
    return 0 === this.recordData.length;
  }

  public equals(data: Data): boolean {
    return data instanceof RecordCollectionData && this.recordData === data.recordData;
  }

  public normalize(): NormalizedRecordCollectionData {
    return this.recordData.map((recordCode: RecordCode) => recordCode.stringValue());
  }
}

export default RecordCollectionData;
export const create = RecordCollectionData.create;
export const denormalize = RecordCollectionData.createFromNormalized;
