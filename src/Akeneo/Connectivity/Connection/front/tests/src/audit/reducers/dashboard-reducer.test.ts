import {connectionsAuditDataFetched, sourceConnectionsFetched} from '@src/audit/actions/dashboard-actions';
import {reducer, State} from '@src/audit/reducers/dashboard-reducer';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {FlowType} from '@src/model/flow-type.enum';

describe('Dashboard reducer', () => {
    it('handles SOURCE_CONNECTIONS_FETCHED action', () => {
        const initialState: State = {
            sourceConnections: {},
            events: {
                [AuditEventType.PRODUCT_CREATED]: {},
                [AuditEventType.PRODUCT_UPDATED]: {},
            },
        };

        const action = sourceConnectionsFetched([
            {
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
            },
            {
                code: 'bynder',
                label: 'Bynder',
                flowType: FlowType.DATA_SOURCE,
                image: null,
            },
        ]);

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            sourceConnections: {
                franklin: {
                    code: 'franklin',
                    label: 'Franklin',
                    flowType: FlowType.DATA_SOURCE,
                    image: null,
                },
                bynder: {
                    code: 'bynder',
                    label: 'Bynder',
                    flowType: FlowType.DATA_SOURCE,
                    image: null,
                },
            },
            events: {
                [AuditEventType.PRODUCT_CREATED]: {},
                [AuditEventType.PRODUCT_UPDATED]: {},
            },
        });
    });

    it('handles CONNECTIONS_AUDIT_DATA_FETCHED action', () => {
        const initialState: State = {
            sourceConnections: {},
            events: {
                [AuditEventType.PRODUCT_CREATED]: {},
                [AuditEventType.PRODUCT_UPDATED]: {},
            },
        };

        const action = connectionsAuditDataFetched(AuditEventType.PRODUCT_CREATED, {
            '<all>': {
                '31-12-2019': 0,
                '01-01-2020': 10,
            },
            franklin: {
                '31-12-2019': 0,
            },
            bynder: {
                '01-01-2020': 10,
            },
        });

        const newState = reducer(initialState, action);

        expect(newState).toStrictEqual({
            sourceConnections: {},
            events: {
                [AuditEventType.PRODUCT_CREATED]: {
                    '<all>': {
                        '31-12-2019': 0,
                        '01-01-2020': 10,
                    },
                    franklin: {
                        '31-12-2019': 0,
                    },
                    bynder: {
                        '01-01-2020': 10,
                    },
                },
                [AuditEventType.PRODUCT_UPDATED]: {},
            },
        });
    });
});
