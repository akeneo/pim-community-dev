import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, String} from 'pimfront/product-grid/domain/model/filter/value';

export default class StartsWith extends BaseOperator {
  readonly identifier: string = 'STARTS WITH';
  readonly needValue: boolean = true;

  public static create(): StartsWith {
    return new StartsWith();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof String;
  }
}
