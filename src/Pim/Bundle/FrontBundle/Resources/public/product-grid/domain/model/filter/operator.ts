export interface Operator {
  readonly identifier: string;
  readonly needValue: boolean;
}

export abstract class BaseOperator implements Operator {
  readonly identifier: string;
  readonly needValue: boolean;
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
