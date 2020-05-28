import {useMemo, useState} from 'react';
import {FlowType} from '../../model/flow-type.enum';
import {useTranslate} from '../../shared/translate';
import {useDashboardState} from '../dashboard-context';

const useConnectionSelect = (flowType: FlowType.DATA_DESTINATION | FlowType.DATA_SOURCE) => {
    const translate = useTranslate();

    const state = useDashboardState();
    const connections = useMemo(() => {
        const filteredConnections = Object.values(state.connections).filter(
            connection => connection.flowType === flowType
        );
        filteredConnections.unshift({
            code: '<all>',
            label: translate('akeneo_connectivity.connection.dashboard.connection_selector.all'),
            flowType,
            image: null,
            auditable: true,
        });

        return filteredConnections;
    }, [state.connections, flowType, translate]);

    const [connectionCode, selectConnectionCode] = useState<string>('<all>');

    return {
        connections,
        connectionCode,
        selectConnectionCode,
    };
};

export default useConnectionSelect;
