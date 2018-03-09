import Filter, {PropertyFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Boolean as BooleanValue} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator, BaseOperator, All} from 'pimfront/product-grid/domain/model/filter/operator';
import {PropertyInterface} from 'pimfront/product-grid/domain/model/field';

class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }
}

export default class Status extends PropertyFilter {
  private static operators: Operator[] = [All.create(), Equal.create()];

  public static createEmpty(property: PropertyInterface): Filter {
    return new Status(property, All.create(), BooleanValue.true());
  }

  getOperators(): Operator[] {
    return Status.operators;
  }
}
