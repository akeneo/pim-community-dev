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
