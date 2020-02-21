import {WrongCredentialsCombinations} from '../../model/wrong-credentials-combinations';

export const WRONG_CREDENTIALS_COMBINATIONS_FETCHED = 'WRONG_CREDENTIALS_COMBINATIONS_FETCHED';
interface WrongCredentialsCombinationsFetchedAction {
    type: typeof WRONG_CREDENTIALS_COMBINATIONS_FETCHED;
    payload: WrongCredentialsCombinations;
}
export const wrongCredentialsCombinationsFetched = (
    payload: WrongCredentialsCombinations
): WrongCredentialsCombinationsFetchedAction => ({
    type: WRONG_CREDENTIALS_COMBINATIONS_FETCHED,
    payload,
});

export type WrongCredentialsCombinationsActions = WrongCredentialsCombinationsFetchedAction;
