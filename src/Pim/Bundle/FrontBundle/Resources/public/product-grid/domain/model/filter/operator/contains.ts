import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class Contains extends BaseOperator {
  readonly identifier: string = 'CONTAINS';
  readonly needValue: boolean = true;

  public static create(): Contains {
    return new Contains();
  }
}
