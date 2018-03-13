import {Field, PropertyInterface, AttributeInterface} from 'pimfront/product-grid/domain/model/field';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value} from 'pimfront/product-grid/domain/model/filter/value';
import {InvalidArgument} from 'pimfront/product-grid/domain/model/error';

export class NormalizedFilter {
  private constructor(readonly field: string, readonly operator: string, readonly value: any) {}

  public static create({field, operator, value}: {field: string; operator: string; value: any}) {
    if (undefined === field || undefined === operator || undefined === value) {
      throw new InvalidArgument(`The given normalized filter is not valid. Arguments: ${JSON.stringify(arguments)}`);
    }

    return new NormalizedFilter(field, operator, value);
  }
}

export default interface Filter {
  field: Field;
  operator: Operator;
  value: Value;
  isEmpty: () => boolean;
  getOperators(): Operator[];
  normalize(): NormalizedFilter;
};

export abstract class BaseFilter implements Filter {
  readonly field: Field;
  readonly operator: Operator;
  readonly value: Value;

  protected constructor(field: Field, operator: Operator, value: Value) {
    if (!this.getOperators().find((currentOperator: Operator) => currentOperator.equals(operator))) {
      throw new InvalidArgument(`The operator given to create the "${field.identifier}" filter is not valid.
Given: ${operator.identifier}
Supported: ${this.getOperators().map((mappedOperator: Operator) => `"${mappedOperator.identifier}"`)}
`);
    }
    this.field = field;
    this.operator = operator;
    this.value = value;
  }

  isEmpty(): boolean {
    return this.operator.needValue && this.value.isEmpty();
  }

  abstract getOperators(): Operator[];

  normalize(): NormalizedFilter {
    return NormalizedFilter.create({
      field: this.field.identifier,
      operator: this.operator.identifier,
      value: this.value.getValue(),
    });
  }

  public static createEmpty(field: Field): Filter {
    throw Error('You need to implement the createEmpty method');
  }

  public static create(field: Field, operator: Operator, value: Value): Filter {
    throw Error('You need to implement the create method');
  }
}

export abstract class PropertyFilter extends BaseFilter {
  readonly field: PropertyInterface;

  public static createEmpty(property: PropertyInterface): Filter {
    throw Error('You need to implement the createEmpty method');
  }

  public static create(property: PropertyInterface, operator: Operator, value: Value): Filter {
    throw Error('You need to implement the create method');
  }
}

export abstract class AttributeFilter extends BaseFilter {
  readonly field: AttributeInterface;

  public static createEmpty(attribute: AttributeInterface): Filter {
    throw Error('You need to implement the createEmpty method');
  }

  public static create(attribute: AttributeInterface, operator: Operator, value: Value): Filter {
    throw Error('You need to implement the create method');
  }
}
