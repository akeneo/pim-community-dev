import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';
import {attributeOptionsReducer, localesReducer} from '../reducers';
import {AttributeOption, Locale} from '../model';

export interface AttributeOptionsState {
    locales: Locale[];
    attributeOptions: AttributeOption[] | null;
}

const composeEnhancers = composeWithDevTools({
    name: 'Akeneo PIM / Attribute edit form / attribute options / Store',
});

export const createStoreWithInitialState = (initialState = {}) => createStore(
    combineReducers({
        locales: localesReducer,
        attributeOptions: attributeOptionsReducer,
    }),
    initialState,
    composeEnhancers(applyMiddleware()),
);

const attributeOptionsStore: Store = createStoreWithInitialState({
    locales: [],
    attributeOptions: null,
});

export default attributeOptionsStore;
