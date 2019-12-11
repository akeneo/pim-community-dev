import {SourceApp} from '../model/source-app';

export const SOURCE_APPS_FETCHED = 'APPS_FETCHED';
interface SourceAppsFetchedAction {
    type: typeof SOURCE_APPS_FETCHED;
    payload: SourceApp[];
}
export const appsFetched = (payload: SourceApp[]): SourceAppsFetchedAction => ({
    type: SOURCE_APPS_FETCHED,
    payload,
});

export type Actions = SourceAppsFetchedAction;
