import {AttributeFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Null, Boolean as BooleanValue, Value} from 'pimfront/product-grid/domain/model/filter/value';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal';
import {AttributeInterface} from 'pimfront/product-grid/domain/model/field';

export interface Choice {
  identifier: string;
  value: {
    operator: Operator;
    value: Value;
  };
}

export default class Boolean extends AttributeFilter {
  private static operators: Operator[] = [All.create(), Equal.create()];

  public static createEmpty(attribute: AttributeInterface) {
    return new Boolean(attribute, All.create(), Null.null());
  }

  public static create(attribute: AttributeInterface, operator: Operator, value: Value) {
    return new Boolean(attribute, operator, value);
  }

  getOperators(): Operator[] {
    return Boolean.operators;
  }

  getChoices() {
    return [
      {
        identifier: 'all',
        value: {
          operator: All.create(),
          value: Null.null(),
        },
      },
      {
        identifier: 'yes',
        value: {
          operator: Equal.create(),
          value: BooleanValue.true(),
        },
      },
      {
        identifier: 'no',
        value: {
          operator: Equal.create(),
          value: BooleanValue.false(),
        },
      },
    ];
  }

  toChoice(): Choice {
    const choices = this.getChoices();

    const choice = choices.find(
      (currentChoice: Choice) =>
        currentChoice.value.operator.equals(this.operator) && currentChoice.value.value.equals(this.value)
    );

    if (undefined === choice) {
      throw new Error(`Cannot find choice for filter ${this.field.identifier}`);
    }

    return choice;
  }

  getFilterFromChoice(choice: Choice): Boolean {
    if (undefined === choice) {
      throw new Error('You need to provide a choice to create a filter');
    }

    return Boolean.create(this.field, choice.value.operator, choice.value.value);
  }
}
