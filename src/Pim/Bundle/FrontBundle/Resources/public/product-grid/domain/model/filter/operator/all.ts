import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class All extends BaseOperator {
  readonly identifier: string = 'ALL';
  readonly needValue: boolean = false;

  public static create(): All {
    return new All();
  }
}
