import {applyMiddleware, combineReducers, createStore, Store} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';
import {attributeOptionsReducer, localesReducer} from '../reducers';
import {AttributeOption, Locale} from '../model';

export interface AttributeOptionsState {
    locales: Locale[];
    attributeOptions: AttributeOption[];
}

const composeEnhancers = composeWithDevTools({
    name: 'Akeneo PIM / Attribute edit form / attribute options / Store',
});

const attributeOptionsStore: Store = createStore(
    combineReducers({
        locales: localesReducer,
        attributeOptions: attributeOptionsReducer,
    }),
    {
        locales: [],
        attributeOptions: [],
    },
    composeEnhancers(applyMiddleware()),
);

export default attributeOptionsStore;
