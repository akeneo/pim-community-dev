import {InvalidArgument} from 'pimfront/product-grid/domain/model/error';

export interface Value {
  toString: () => string;
  getValue: () => any;
  isEmpty: () => boolean;
  equals: (value: Value) => boolean;
}

export class String implements Value {
  readonly value: string;

  private constructor(value: string) {
    this.value = value;
  }

  public static fromValue(value: string): String {
    if (typeof value !== 'string') {
      throw new InvalidArgument(`A string value is required to create a String value (${typeof value} given)`);
    }

    return new String(value);
  }

  public static empty(): String {
    return new String('');
  }

  public toString() {
    return this.getValue();
  }

  public getValue() {
    return this.value;
  }

  public isEmpty() {
    return 0 === this.value.length;
  }

  public equals(value: Value): boolean {
    return value instanceof String && value.getValue() === this.getValue();
  }
}

export class Boolean implements Value {
  readonly value: boolean;

  private constructor(value: boolean) {
    this.value = value;
  }

  public static fromValue(value: boolean): Boolean {
    if (typeof value !== 'boolean') {
      throw new InvalidArgument(`A boolean value is required to create a Boolean value (${typeof value} given)`);
    }

    return new Boolean(value);
  }

  public static true(): Boolean {
    return new Boolean(true);
  }

  public static false(): Boolean {
    return new Boolean(false);
  }

  public toString() {
    return this.value ? 'true' : 'false';
  }

  public getValue(): any {
    return this.value;
  }

  public isEmpty() {
    return false;
  }

  public equals(value: Value): boolean {
    return value instanceof Boolean && value.getValue() === this.getValue();
  }
}

export class Null implements Value {
  public static null(): Null {
    return new Null();
  }

  public static fromValue(value: null): Null {
    if (value !== null) {
      throw new InvalidArgument(`A null value is required to create a Null value (${typeof value} given)`);
    }

    return new Null();
  }

  public toString() {
    return 'null';
  }

  public getValue(): null {
    return null;
  }

  public isEmpty() {
    return true;
  }

  public equals(value: Value): boolean {
    return value instanceof Null && value.getValue() === this.getValue();
  }
}

export class Collection<CollectionElement> implements Value {
  readonly values: CollectionElement[];

  private constructor(values: any[]) {
    this.values = values;
  }

  public static fromValue<CollectionElement>(values: any[]): Collection<CollectionElement> {
    return new Collection<CollectionElement>(values);
  }

  public static empty<CollectionElement>(): Collection<CollectionElement> {
    return new Collection<CollectionElement>([]);
  }

  public toString() {
    return this.values.join(', ');
  }

  public getValue(): any {
    return this.values;
  }

  public isEmpty() {
    return 0 === this.values.length;
  }

  public equals(value: Value): boolean {
    return (
      value instanceof Collection &&
      value.getValue().length === this.getValue().length &&
      value.toString() === this.toString()
    );
  }
}
