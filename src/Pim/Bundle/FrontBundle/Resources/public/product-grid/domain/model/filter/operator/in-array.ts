import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class InArray extends BaseOperator {
  readonly identifier: string = 'IN';
  readonly needValue: boolean = true;

  public static create(): InArray {
    return new InArray();
  }
}
