import {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {useDashboardState} from './dashboard-context';
import {FlowType} from '../model/flow-type.enum';
import {useTranslate} from '../shared/translate';
import {Connection} from '../model/connection';

const useConnectionSelect = (
    flowType: FlowType.DATA_DESTINATION | FlowType.DATA_SOURCE
): [Connection[], string | undefined, Dispatch<SetStateAction<string | undefined>>] => {
    const state = useDashboardState();
    const translate = useTranslate();
    const [selectedConnectionCode, setSelectedConnectionCode] = useState<string>();
    const connections = Object.values(state.connections).filter(connection => connection.flowType === flowType);

    useEffect(() => {
        if (0 === connections.length) {
            setSelectedConnectionCode(undefined);
        } else if (connections.length > 0 && undefined === selectedConnectionCode) {
            setSelectedConnectionCode('<all>');
        }
    }, [connections, selectedConnectionCode]);

    connections.unshift({
        code: '<all>',
        label: translate('akeneo_connectivity.connection.dashboard.connection_selector.all'),
        flowType,
        image: null,
    });

    return [connections, selectedConnectionCode, setSelectedConnectionCode];
};

export default useConnectionSelect;
