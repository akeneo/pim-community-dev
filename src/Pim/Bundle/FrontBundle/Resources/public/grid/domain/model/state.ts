import Query, { createQuery } from './query';

export default interface GridState<Element> {
  readonly query: Query;
  readonly items: Element[];
}

class ConcreteGridState<Element> implements GridState<Element> {
  readonly query: Query;
  readonly items: Element[];

  public constructor (query: Query, items: Element[] = []) {
    this.query = query;
    this.items = items;
  }
};

export const createState = <Element>(rawState: any): GridState<Element> => {
  return new ConcreteGridState(
    rawState.query ? rawState.query : createQuery({}),
    rawState.items
  );
};
