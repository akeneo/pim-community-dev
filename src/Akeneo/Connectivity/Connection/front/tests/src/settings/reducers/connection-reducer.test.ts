import {FlowType} from '@src/model/flow-type.enum';
import {
    connectionDeleted,
    connectionFetched,
    connectionPasswordRegenerated,
    connectionsFetched,
    connectionUpdated,
} from '@src/settings/actions/connections-actions';
import {reducer, State} from '@src/settings/reducers/connections-reducer';

describe('Connections reducer', () => {
    it('handles CONNECTIONS_FETCHED action', () => {
        const initialState: State = {
            magento: {
                code: 'magento',
                label: 'Test',
                flowType: FlowType.OTHER,
                image: null,
                auditable: false,
                clientId: '<clientId>',
                secret: '<secret>',
                username: 'magento_1234',
                password: null,
                userRoleId: '1',
                userGroupId: null,
            },
        };

        const action = connectionsFetched([
            {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
            },
            {
                code: 'magento',
                label: 'Magento',
                flowType: FlowType.DATA_DESTINATION,
                image: 'a/b/c/magento.png',
                auditable: false,
            },
        ]);

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            franklin: {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
                clientId: '',
                secret: '',
                username: '',
                password: null,
                userRoleId: '',
                userGroupId: null,
            },
            magento: {
                code: 'magento',
                label: 'Magento',
                flowType: FlowType.DATA_DESTINATION,
                image: 'a/b/c/magento.png',
                auditable: false,
                clientId: '<clientId>',
                secret: '<secret>',
                username: 'magento_1234',
                password: null,
                userRoleId: '1',
                userGroupId: null,
            },
        });
    });

    it('handles CONNECTION_FETCHED action', () => {
        const initialState: State = {};

        const action = connectionFetched({
            code: 'magento',
            label: 'Magento',
            flowType: FlowType.DATA_DESTINATION,
            image: 'a/b/c/magento.png',
            auditable: false,
            clientId: '<clientId>',
            secret: '<secret>',
            username: 'magento_1234',
            password: '<password>',
            userRoleId: '1',
            userGroupId: '2',
        });

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            magento: {
                code: 'magento',
                label: 'Magento',
                flowType: FlowType.DATA_DESTINATION,
                image: 'a/b/c/magento.png',
                auditable: false,
                clientId: '<clientId>',
                secret: '<secret>',
                username: 'magento_1234',
                password: '<password>',
                userRoleId: '1',
                userGroupId: '2',
            },
        });
    });

    it('handles CONNECTION_FETCHED action and keep the password', () => {
        const initialState: State = {
            franklin: {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
                clientId: '',
                secret: '',
                username: '',
                password: '<password>',
                userRoleId: '',
                userGroupId: null,
            },
        };

        const action = connectionFetched({
            code: 'franklin',
            label: 'Franklin',
            flowType: FlowType.DATA_SOURCE,
            image: null,
            auditable: false,
            clientId: '<clientId>',
            secret: '<secret>',
            username: 'franklin_1234',
            password: null,
            userRoleId: '1',
            userGroupId: null,
        });

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            franklin: {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
                clientId: '<clientId>',
                secret: '<secret>',
                username: 'franklin_1234',
                password: '<password>',
                userRoleId: '1',
                userGroupId: null,
            },
        });
    });

    it('handles CONNECTION_UPDATED action', () => {
        const initialState: State = {
            magento: {
                code: 'magento',
                label: 'Test',
                flowType: FlowType.OTHER,
                image: null,
                auditable: false,
                clientId: '',
                secret: '',
                username: '',
                password: null,
                userRoleId: '1',
                userGroupId: null,
            },
        };

        const action = connectionUpdated({
            code: 'magento',
            label: 'Magento',
            flowType: FlowType.DATA_DESTINATION,
            image: 'a/b/c/magento.png',
            auditable: true,
            userRoleId: '2',
            userGroupId: '3',
        });

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            magento: {
                code: 'magento',
                label: 'Magento',
                flowType: FlowType.DATA_DESTINATION,
                image: 'a/b/c/magento.png',
                auditable: true,
                clientId: '',
                secret: '',
                username: '',
                password: null,
                userRoleId: '2',
                userGroupId: '3',
            },
        });
    });

    it('handles CONNECTION_DELETED action', () => {
        const initialState: State = {
            franklin: {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.OTHER,
                image: null,
                auditable: false,
                clientId: '',
                secret: '',
                username: '',
                password: null,
                userRoleId: '1',
                userGroupId: null,
            },
        };

        const action = connectionDeleted('franklin');

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({});
    });

    it('handles CONNECTION_PASSWORD_REGENERATED action', () => {
        const initialState: State = {
            franklin: {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
                auditable: false,
                clientId: '',
                secret: '',
                username: '',
                password: null,
                userRoleId: '1',
                userGroupId: null,
            },
        };

        const action = connectionPasswordRegenerated('franklin', '<password>');

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            franklin: {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
                auditable: false,
                clientId: '',
                secret: '',
                username: '',
                password: '<password>',
                userRoleId: '1',
                userGroupId: null,
            },
        });
    });
});
