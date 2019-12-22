import {AppCredentials} from '../../domain/apps/app-credentials.interface';
import {App} from '../../domain/apps/app.interface';

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

export const APP_DELETED = 'APP_DELETED';
interface AppDeletedAction {
    type: typeof APP_DELETED;
    payload: string;
}
export const appDeleted = (code: string): AppDeletedAction => ({
    type: APP_DELETED,
    payload: code,
});

export const APP_PASSWORD_REGENERATED = 'APP_PASSWORD_REGENERATED';
type AppPasswordRegeneratedAction = {
    type: typeof APP_PASSWORD_REGENERATED;
    payload: {code: string; password: string};
};
export const appPasswordRegenerated = (code: string, password: string): AppPasswordRegeneratedAction => ({
    type: APP_PASSWORD_REGENERATED,
    payload: {code, password},
});

export type Actions =
    | AppsFetchedAction
    | AppWithCredentialsFetchedAction
    | AppUpdatedAction
    | AppDeletedAction
    | AppPasswordRegeneratedAction;
