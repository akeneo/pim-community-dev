export interface Operator {
  readonly identifier: string;
  readonly needValue: boolean;

  equals(operator: Operator): boolean;
}

export abstract class BaseOperator implements Operator {
  readonly identifier: string;
  readonly needValue: boolean;

  public equals(operator: Operator): boolean {
    return this.identifier === operator.identifier;
  }
}

export class All extends BaseOperator {
  readonly identifier: string = 'ALL';
  readonly needValue: boolean = false;

  public static create(): All {
    return new All();
  }
}

export class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }
}

export class InArray extends BaseOperator {
  readonly identifier: string = 'IN';
  readonly needValue: boolean = true;

  public static create(): InArray {
    return new InArray();
  }
}
