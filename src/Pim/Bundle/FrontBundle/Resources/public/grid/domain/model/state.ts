import Query, { createQuery } from './query';

export default interface GridState<Element> {
  readonly query: Query;
  readonly items: Element[];
  readonly isFetching: boolean;
}

class ConcreteGridState<Element> implements GridState<Element> {
  readonly query: Query;
  readonly items: Element[];
  readonly isFetching: boolean;

  public constructor (query: Query, items: Element[] = [], isFetching: boolean = false) {
    this.query     = query;
    this.items     = items;
    this.isFetching = isFetching;
  }
};

export const createState = <Element>(rawState: any): GridState<Element> => {
  return new ConcreteGridState(
    rawState.query ? rawState.query : createQuery({filters: [
      {
        field: 'family',
        operator: 'IN',
        value: ['clothing']
      }
    ]}),
    rawState.items,
    rawState.isFetching
  );
};
