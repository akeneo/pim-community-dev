import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class StartsWith extends BaseOperator {
  readonly identifier: string = 'IS EMPTY';
  readonly needValue: boolean = false;

  public static create(): StartsWith {
    return new StartsWith();
  }
}
