import {Value} from 'pimfront/product-grid/domain/model/filter/value';

export interface Operator {
  readonly identifier: string;
  readonly needValue: boolean;

  equals(operator: Operator): boolean;
  supportsValue(value: Value): boolean;
}

export abstract class BaseOperator implements Operator {
  readonly identifier: string;
  readonly needValue: boolean;

  public equals(operator: Operator): boolean {
    return this.identifier === operator.identifier;
  }

  public abstract supportsValue(value: Value): boolean;
}
