import {Action, ActionCreator, Reducer} from 'redux';
import {Locale} from '../model';

interface InitializeLocalesAction extends Action {
    payload: {
        locales: Locale[];
    };
}

const INITIALIZE_LOCALES = 'INITIALIZE_LOCALES';
export const initializeLocalesAction: ActionCreator<InitializeLocalesAction> = (locales: Locale[]) => {
    return {
        type: INITIALIZE_LOCALES,
        payload: {
            locales,
        }
    };
};

const localesReducer: Reducer<Locale[]> = (previousState = [], {type, payload}) => {
    switch (type) {
    case INITIALIZE_LOCALES: {
        return [
            ...payload.locales
        ];
    }
    default:
        return previousState;
    }
};
export default localesReducer;
