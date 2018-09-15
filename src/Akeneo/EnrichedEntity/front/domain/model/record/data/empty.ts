import Data from 'akeneoenrichedentity/domain/model/record/data';

export type NormalizedEmptyData = null;

export default class EmptyData extends Data {
  private constructor() {
    super();

    Object.freeze(this);
  }

  public static create(): EmptyData {
    return new EmptyData();
  }

  public static createFromNormalized(): EmptyData {
    return new EmptyData();
  }

  public normalize(): null {
    return null;
  }

  public isEmpty(): boolean {
    return true;
  }
}

export const create = EmptyData.create;
export const denormalize = EmptyData.createFromNormalized;
