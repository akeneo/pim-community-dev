import {AttributeFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Boolean as BooleanValue} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator, BaseOperator, All} from 'pimfront/product-grid/domain/model/filter/operator';
import {Field} from 'pimfront/product-grid/domain/model/field';

class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }
}

export default class Boolean extends AttributeFilter {
  private static operators: Operator[] = [All.create(), Equal.create()];

  public static createEmptyFromAttribute(attribute: Field) {
    return new Boolean(attribute, All.create(), BooleanValue.empty());
  }

  getOperators(): Operator[] {
    return Boolean.operators;
  }
}
