export class InvalidArgument extends Error {}

export interface Filter {
  field: string;
  operator: string;
  value: any;
  context: any;
}

export interface Query {
  readonly filters: Filter[];
  readonly page: number;
  readonly size: number;
}
