import {AttributeFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Null, Value} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import Contains from 'pimfront/product-grid/domain/model/filter/operator/contains';
import DoesNotContain from 'pimfront/product-grid/domain/model/filter/operator/does-not-contain';
import StartsWith from 'pimfront/product-grid/domain/model/filter/operator/starts-with';
import IsEmpty from 'pimfront/product-grid/domain/model/filter/operator/is-empty';
import InList from 'pimfront/product-grid/domain/model/filter/operator/in-list';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal-string';
import {AttributeInterface} from 'pimfront/product-grid/domain/model/field';

type SupportedOperator = All | Equal | Contains | DoesNotContain | StartsWith | IsEmpty | InList;

export default class Text extends AttributeFilter {
  private static operators: SupportedOperator[] = [
    All.create(),
    Equal.create(),
    Contains.create(),
    DoesNotContain.create(),
    StartsWith.create(),
    IsEmpty.create(),
    InList.create(),
  ];

  public static createEmpty(attribute: AttributeInterface) {
    return new Text(attribute, All.create(), Null.null());
  }

  public static create(attribute: AttributeInterface, operator: Operator, value: Value): Text {
    return new Text(attribute, operator, value);
  }

  public setOperator(operator: Operator): Text {
    const value = operator.supportsValue(this.value) ? this.value : operator.defaultValue();

    return Text.create(this.field, operator, value);
  }

  public setValue(value: Value): Text {
    return Text.create(this.field, this.operator, value);
  }

  getOperators(): Operator[] {
    return Text.operators;
  }
}
