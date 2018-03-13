import {PropertyFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Null as NullValue, Value} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal';
import {PropertyInterface} from 'pimfront/product-grid/domain/model/field';

export default class Boolean extends PropertyFilter {
  private static operators: Operator[] = [All.create(), Equal.create()];

  public static createEmpty(attribute: PropertyInterface) {
    return new Boolean(attribute, All.create(), NullValue.null());
  }

  public static create(attribute: PropertyInterface, operator: Operator, value: Value) {
    return new Boolean(attribute, operator, value);
  }

  getOperators(): Operator[] {
    return Boolean.operators;
  }
}
