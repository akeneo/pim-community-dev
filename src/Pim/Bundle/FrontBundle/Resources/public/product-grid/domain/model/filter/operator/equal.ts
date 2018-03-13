import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }
}
