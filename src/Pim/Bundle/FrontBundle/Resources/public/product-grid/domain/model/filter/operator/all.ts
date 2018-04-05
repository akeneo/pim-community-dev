import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, Null} from 'pimfront/product-grid/domain/model/filter/value';

export default class All extends BaseOperator {
  readonly identifier: string = 'ALL';
  readonly needValue: boolean = false;

  public static create(): All {
    return new All();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof Null;
  }

  public defaultValue(): Null {
    return Null.fromValue(null);
  }
}
