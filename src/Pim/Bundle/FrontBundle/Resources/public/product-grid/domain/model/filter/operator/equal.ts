import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, String, Boolean} from 'pimfront/product-grid/domain/model/filter/value';

export default class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof String || value instanceof Boolean;
  }

  public defaultValue(): String {
    return String.fromValue('');
  }
}
