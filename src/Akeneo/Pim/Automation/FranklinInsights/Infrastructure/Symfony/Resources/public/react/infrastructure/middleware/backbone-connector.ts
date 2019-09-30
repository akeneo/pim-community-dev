import {Store} from 'redux';

export const createBackboneConnectorMiddleware = (callback: (state: any) => void) => (store: Store) => (next: any) => (
  action: any
) => {
  const result = next(action);

  callback(store.getState());

  return result;
};
