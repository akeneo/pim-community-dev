import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';

import pageContextReducer from '../reducer/AttributeEditForm/pageContextReducer';

const composeEnhancers = composeWithDevTools({
  name: 'Akeneo PIM / Attribute Edit Form / Data Quality Insights / Store',
});

export const createStoreWithInitialState = (initialState = undefined) =>
  createStore(
    combineReducers({
      pageContext: pageContextReducer,
    }),
    initialState,
    composeEnhancers(applyMiddleware())
  );

const attributeEditFormStore: Store<any, any> = createStoreWithInitialState();

export default attributeEditFormStore;
