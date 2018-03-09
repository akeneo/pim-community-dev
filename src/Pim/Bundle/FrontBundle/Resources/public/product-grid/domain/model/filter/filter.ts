import {Field, PropertyInterface, AttributeInterface} from 'pimfront/product-grid/domain/model/field';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value} from 'pimfront/product-grid/domain/model/filter/value';

export interface NormalizedFilter {
  field: string;
  operator: string;
  value: any;
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
    this.field = field;
    this.operator = operator;
    this.value = value;
  }

  isEmpty(): boolean {
    return this.operator.needValue && this.value.isEmpty();
  }

  abstract getOperators(): Operator[];

  normalize(): NormalizedFilter {
    return {
      field: this.field.identifier,
      operator: this.operator.identifier,
      value: this.value.getValue(),
    };
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
