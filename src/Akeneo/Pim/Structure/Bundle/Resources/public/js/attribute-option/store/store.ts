import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';
import {attributeOptionsReducer} from '../reducers';
import {AttributeOption} from '../model';

export interface AttributeOptionsState {
  attributeOptions: AttributeOption[] | null;
}

const composeEnhancers = composeWithDevTools({
  name: 'Akeneo PIM / Attribute edit form / attribute options / Store',
});

export const createStoreWithInitialState = (initialState = {}) =>
  createStore(
    combineReducers({
      attributeOptions: attributeOptionsReducer,
    }),
    initialState,
    composeEnhancers(applyMiddleware())
  );

const attributeOptionsStore: Store = createStoreWithInitialState({
  attributeOptions: null,
});

export default attributeOptionsStore;
