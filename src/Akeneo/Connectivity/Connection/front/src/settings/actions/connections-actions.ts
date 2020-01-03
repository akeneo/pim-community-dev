import {ConnectionCredentials} from '../../model/connection-credentials';
import {Connection} from '../../model/connection';

export const CONNECTIONS_FETCHED = 'CONNECTIONS_FETCHED';
interface ConnectionsFetchedAction {
    type: typeof CONNECTIONS_FETCHED;
    payload: Connection[];
}
export const connectionsFetched = (payload: Connection[]): ConnectionsFetchedAction => ({
    type: CONNECTIONS_FETCHED,
    payload,
});

export const CONNECTION_WITH_CREDENTIALS_FETCHED = 'CONNECTION_WITH_CREDENTIALS_FETCHED';
interface ConnectionWithCredentialsFetchedAction {
    type: typeof CONNECTION_WITH_CREDENTIALS_FETCHED;
    payload: Connection & ConnectionCredentials;
}
export const connectionWithCredentialsFetched = (
    payload: Connection & ConnectionCredentials
): ConnectionWithCredentialsFetchedAction => ({
    type: CONNECTION_WITH_CREDENTIALS_FETCHED,
    payload,
});

export const CONNECTION_UPDATED = 'CONNECTION_UPDATED';
interface ConnectionUpdatedAction {
    type: typeof CONNECTION_UPDATED;
    payload: Connection;
}
export const connectionUpdated = (payload: Connection): ConnectionUpdatedAction => ({
    type: CONNECTION_UPDATED,
    payload,
});

export const CONNECTION_DELETED = 'CONNECTION_DELETED';
interface ConnectionDeletedAction {
    type: typeof CONNECTION_DELETED;
    payload: string;
}
export const connectionDeleted = (code: string): ConnectionDeletedAction => ({
    type: CONNECTION_DELETED,
    payload: code,
});

export const CONNECTION_PASSWORD_REGENERATED = 'CONNECTION_PASSWORD_REGENERATED';
type ConnectionPasswordRegeneratedAction = {
    type: typeof CONNECTION_PASSWORD_REGENERATED;
    payload: {code: string; password: string};
};
export const connectionPasswordRegenerated = (code: string, password: string): ConnectionPasswordRegeneratedAction => ({
    type: CONNECTION_PASSWORD_REGENERATED,
    payload: {code, password},
});

export type Actions =
    | ConnectionsFetchedAction
    | ConnectionWithCredentialsFetchedAction
    | ConnectionUpdatedAction
    | ConnectionDeletedAction
    | ConnectionPasswordRegeneratedAction;
