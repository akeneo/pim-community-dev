import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, String} from 'pimfront/product-grid/domain/model/filter/value';

export default class DoesNotContain extends BaseOperator {
  readonly identifier: string = 'DOES NOT CONTAIN';
  readonly needValue: boolean = true;

  public static create(): DoesNotContain {
    return new DoesNotContain();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof String;
  }

  public defaultValue(): String {
    return String.fromValue('');
  }
}
