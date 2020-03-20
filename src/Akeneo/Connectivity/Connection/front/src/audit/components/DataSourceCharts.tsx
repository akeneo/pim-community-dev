import React, {useEffect, useState} from 'react';
import {EventChart} from './EventChart';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {blueTheme, purpleTheme} from '../event-chart-themes';
import {Translate, useTranslate} from '../../shared/translate';
import {Section} from '../../common';
import {ConnectionSelect} from './ConnectionSelect';
import styled from 'styled-components';
import {useDashboardState} from '../dashboard-context';

const DataSourceChartsContainer = styled.div`
    padding-bottom: 25px;
    display: block;
`;
const ChartsContainer = styled.div`
    display: flex;
    justify-content: space-between;
    flex-direction: row;
`;

export const DataSourceCharts = () => {
    const state = useDashboardState();
    const translate = useTranslate();

    const [selectedConnectionCode, setSelectedConnectionCode] = useState<string>();
    useEffect(() => {
        if (0 === Object.keys(state.sourceConnections).length) {
            setSelectedConnectionCode(undefined);
        } else if (Object.keys(state.sourceConnections).length > 0 && undefined === selectedConnectionCode) {
            setSelectedConnectionCode('<all>');
        }
    }, [state.sourceConnections, selectedConnectionCode]);

    const sourceConnections = Object.values(state.sourceConnections);
    sourceConnections.unshift({
        code: '<all>',
        label: translate('akeneo_connectivity.connection.dashboard.connection_selector.all'),
        flowType: sourceConnections[0].flowType,
        image: null,
    });

    return (
        <DataSourceChartsContainer>
            <Section title={<Translate id='akeneo_connectivity.connection.dashboard.charts.inbound' />}>
                <ConnectionSelect connections={sourceConnections} onChange={code => setSelectedConnectionCode(code)} />
            </Section>
            <ChartsContainer>
                <EventChart
                    eventType={AuditEventType.PRODUCT_CREATED}
                    theme={purpleTheme}
                    title={
                        <Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_created' />
                    }
                    selectedConnectionCode={selectedConnectionCode}
                />
                <EventChart
                    eventType={AuditEventType.PRODUCT_UPDATED}
                    theme={blueTheme}
                    title={
                        <Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_updated' />
                    }
                    selectedConnectionCode={selectedConnectionCode}
                />
            </ChartsContainer>
        </DataSourceChartsContainer>
    );
};
