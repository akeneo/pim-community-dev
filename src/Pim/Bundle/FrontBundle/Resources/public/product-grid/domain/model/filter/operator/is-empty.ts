import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class IsEmpty extends BaseOperator {
  readonly identifier: string = 'EMPTY';
  readonly needValue: boolean = false;

  public static create(): IsEmpty {
    return new IsEmpty();
  }
}
