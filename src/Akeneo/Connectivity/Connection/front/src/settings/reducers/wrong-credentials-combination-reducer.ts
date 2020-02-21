import {Reducer} from 'react';
import {WrongCredentialsCombinations} from '../../model/wrong-credentials-combinations';
import {
    WRONG_CREDENTIALS_COMBINATIONS_FETCHED,
    WrongCredentialsCombinationsActions,
} from '../actions/wrong-credentials-combinations-actions';

export const reducer: Reducer<WrongCredentialsCombinations, WrongCredentialsCombinationsActions> = (_, action) => {
    switch (action.type) {
        case WRONG_CREDENTIALS_COMBINATIONS_FETCHED:
            return action.payload;
    }
};

export const initialState: WrongCredentialsCombinations = {};
