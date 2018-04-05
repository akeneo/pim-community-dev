import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, Null} from 'pimfront/product-grid/domain/model/filter/value';

export default class IsEmpty extends BaseOperator {
  readonly identifier: string = 'EMPTY';
  readonly needValue: boolean = false;

  public static create(): IsEmpty {
    return new IsEmpty();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof Null;
  }

  public defaultValue(): Null {
    return Null.fromValue(null);
  }
}
