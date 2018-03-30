import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class InList extends BaseOperator {
  readonly identifier: string = 'IN';
  readonly needValue: boolean = true;

  public static create(): InList {
    return new InList();
  }
}
