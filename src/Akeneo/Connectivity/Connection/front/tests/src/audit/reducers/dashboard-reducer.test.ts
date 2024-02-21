import {connectionsAuditDataFetched, connectionsFetched} from '@src/audit/actions/dashboard-actions';
import {reducer, State} from '@src/audit/reducers/dashboard-reducer';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {FlowType} from '@src/model/flow-type.enum';

describe('Dashboard reducer', () => {
    it('handles CONNECTIONS_FETCHED action', () => {
        const initialState: State = {
            connections: {},
            events: {
                [AuditEventType.PRODUCT_CREATED]: {},
                [AuditEventType.PRODUCT_UPDATED]: {},
                [AuditEventType.PRODUCT_READ]: {},
            },
        };

        const action = connectionsFetched([
            {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: true,
            },
            {
                code: 'bynder',
                label: 'Bynder',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
                auditable: true,
            },
        ]);

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            connections: {
                franklin: {
                    code: 'franklin',
                    label: 'Franklin',
                    flowType: FlowType.DATA_SOURCE,
                    image: null,
                    auditable: true,
                },
                bynder: {
                    code: 'bynder',
                    label: 'Bynder',
                    flowType: FlowType.DATA_DESTINATION,
                    image: null,
                    auditable: true,
                },
            },
            events: {
                [AuditEventType.PRODUCT_CREATED]: {},
                [AuditEventType.PRODUCT_UPDATED]: {},
                [AuditEventType.PRODUCT_READ]: {},
            },
        });
    });

    it('handles CONNECTIONS_AUDIT_DATA_FETCHED action', () => {
        const initialState: State = {
            connections: {},
            events: {
                [AuditEventType.PRODUCT_CREATED]: {},
                [AuditEventType.PRODUCT_UPDATED]: {},
                [AuditEventType.PRODUCT_READ]: {},
            },
        };

        const action = connectionsAuditDataFetched(AuditEventType.PRODUCT_CREATED, {
            '<all>': {
                daily: {
                    '31-12-2019': 0,
                    '01-01-2020': 10,
                },
                weekly_total: 10,
            },
            franklin: {
                daily: {
                    '31-12-2019': 0,
                },
                weekly_total: 0,
            },
            bynder: {
                daily: {
                    '01-01-2020': 10,
                },
                weekly_total: 10,
            },
        });

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            connections: {},
            events: {
                [AuditEventType.PRODUCT_CREATED]: {
                    '<all>': {
                        daily: {
                            '31-12-2019': 0,
                            '01-01-2020': 10,
                        },
                        weekly_total: 10,
                    },
                    franklin: {
                        daily: {
                            '31-12-2019': 0,
                        },
                        weekly_total: 0,
                    },
                    bynder: {
                        daily: {
                            '01-01-2020': 10,
                        },
                        weekly_total: 10,
                    },
                },
                [AuditEventType.PRODUCT_UPDATED]: {},
                [AuditEventType.PRODUCT_READ]: {},
            },
        });
    });
});
