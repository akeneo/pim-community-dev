import {AttributeFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Boolean as BooleanValue, Value} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator, BaseOperator, All} from 'pimfront/product-grid/domain/model/filter/operator';
import {AttributeInterface} from 'pimfront/product-grid/domain/model/field';

class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }
}

export default class Boolean extends AttributeFilter {
  private static operators: Operator[] = [All.create(), Equal.create()];

  public static createEmptyFromAttribute(attribute: AttributeInterface) {
    return new Boolean(attribute, All.create(), BooleanValue.empty());
  }

  public static create(attribute: AttributeInterface, operator: Operator, value: Value) {
      return new Boolean(attribute, operator, value);
  }

  getOperators(): Operator[] {
    return Boolean.operators;
  }
}
