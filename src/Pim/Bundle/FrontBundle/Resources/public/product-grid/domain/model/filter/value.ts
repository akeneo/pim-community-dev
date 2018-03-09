export interface Value {
  toString: () => string;
  getValue: () => any;
  isEmpty: () => boolean;
}

export class String implements Value {
  readonly value: string;

  private constructor(value: string) {
    this.value = value;
  }

  public static fromValue(value: string): String {
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
}

export class Boolean implements Value {
  readonly value: boolean;

  private constructor(value: boolean) {
    this.value = value;
  }

  public static true(): Boolean {
    return new Boolean(true);
  }

  public static false(): Boolean {
    return new Boolean(false);
  }

  public static fromValue(value: boolean): Boolean {
    return new Boolean(value);
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
}

export class Collection implements Value {
  readonly values: Element[];

  private constructor(values: any[]) {
    this.values = values;
  }

  public static fromValue(values: any[]): Collection {
    return new Collection(values);
  }

  public static empty(): Collection {
    return new Collection([]);
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
}
