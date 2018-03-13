import {PropertyFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Value, Collection} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import InArray from 'pimfront/product-grid/domain/model/filter/operator/in-array';
import {Field} from 'pimfront/product-grid/domain/model/field';

export default class Status extends PropertyFilter {
  private static operators: Operator[] = [InArray.create()];

  private constructor(field: Field, operator: Operator, value: Value) {
    super(field, operator, value);
  }

  public static createEmptyFromField(field: Field) {
    return new Status(field, InArray.create(), Collection.empty());
  }

  getOperators() {
    return Status.operators;
  }
}
