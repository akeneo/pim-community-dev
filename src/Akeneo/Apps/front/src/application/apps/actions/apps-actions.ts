import {AppCredentials} from '../../../domain/apps/app-credentials.interface';
import {App} from '../../../domain/apps/app.interface';

export const APPS_FETCHED = 'APPS_FETCHED';
interface AppsFetchedAction {
    type: typeof APPS_FETCHED;
    payload: App[];
}
export const appsFetched = (payload: App[]): AppsFetchedAction => ({
    type: APPS_FETCHED,
    payload,
});

export const APP_WITH_CREDENTIALS_FETCHED = 'APP_WITH_CREDENTIALS_FETCHED';
interface AppWithCredentialsFetchedAction {
    type: typeof APP_WITH_CREDENTIALS_FETCHED;
    payload: App & AppCredentials;
}
export const appWithCredentialsFetched = (payload: App & AppCredentials): AppWithCredentialsFetchedAction => ({
    type: APP_WITH_CREDENTIALS_FETCHED,
    payload,
});

export const APP_UPDATED = 'APP_UPDATED';
interface AppUpdatedAction {
    type: typeof APP_UPDATED;
    payload: App;
}
export const appUpdated = (payload: App): AppUpdatedAction => ({
    type: APP_UPDATED,
    payload,
});

export type Actions = AppsFetchedAction | AppWithCredentialsFetchedAction | AppUpdatedAction;
