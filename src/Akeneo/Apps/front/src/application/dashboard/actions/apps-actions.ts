import {SourceApp} from '../model/source-app';

export const APPS_FETCHED = 'APPS_FETCHED';
interface AppsFetchedAction {
    type: typeof APPS_FETCHED;
    payload: SourceApp[];
}
export const appsFetched = (payload: SourceApp[]): AppsFetchedAction => ({
    type: APPS_FETCHED,
    payload,
});

export type Actions = AppsFetchedAction;
