import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

export default class DoesNotContain extends BaseOperator {
  readonly identifier: string = 'DOES NOT CONTAIN';
  readonly needValue: boolean = true;

  public static create(): DoesNotContain {
    return new DoesNotContain();
  }
}
