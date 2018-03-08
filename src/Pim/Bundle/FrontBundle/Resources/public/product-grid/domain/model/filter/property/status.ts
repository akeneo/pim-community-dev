import Filter from 'pimfront/product-grid/domain/model/filter/filter';
import {Value, Boolean} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator, BaseOperator, All} from 'pimfront/product-grid/domain/model/filter/operator';
import {Field} from 'pimfront/product-grid/domain/model/field';

class Equal extends BaseOperator {
  readonly identifier: string = '=';
  readonly needValue: boolean = true;

  public static create(): Equal {
    return new Equal();
  }
}

export default class Status implements Filter {
  readonly field: Field;
  readonly operator: Operator;
  readonly value: Value;

  private constructor(field: Field, operator: Operator, value: Value) {
    this.field = field;
    this.operator = operator;
    this.value = value;
  }

  private static operators: Operator[] = [All.create(), Equal.create()];

  isEmpty(): boolean {
    return this.operator.needValue && this.value.isEmpty();
  }

  public static createEmptyFromProperty(property: Field) {
    return new Status(property, All.create(), Boolean.empty());
  }

  getOperators(): Operator[] {
    return Status.operators;
  }
}
