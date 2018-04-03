import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, Collection} from 'pimfront/product-grid/domain/model/filter/value';

export default class InList extends BaseOperator {
  readonly identifier: string = 'IN';
  readonly needValue: boolean = true;

  public static create(): InList {
    return new InList();
  }

  public supportsValue(value: Value): boolean {
    return value instanceof Collection;
  }
}
