import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, String} from 'pimfront/product-grid/domain/model/filter/value';

export default class Contains extends BaseOperator {
  readonly identifier: string = 'CONTAINS';
  readonly needValue: boolean = true;

  public static create(): Contains {
    return new Contains();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof String;
  }
}
