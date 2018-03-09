export interface Operator {
  readonly identifier: string;
  readonly needValue: boolean;
}

export abstract class BaseOperator implements Operator {
  readonly identifier: string;
  readonly needValue: boolean;

  public static create(): Operator {
    throw Error('The create method need to be implemented');
  }
}

export class All extends BaseOperator {
  readonly identifier: string = 'ALL';
  readonly needValue: boolean = false;

  public static create(): All {
    return new All();
  }
}

export class InArray extends BaseOperator {
  readonly identifier: string = 'IN';
  readonly needValue: boolean = true;

  public static create(): InArray {
    return new InArray();
  }
}
