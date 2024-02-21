import React from 'react';
import {Loading, SmallHelper} from '../../../common';
import styled from '../../../common/styled-with-theme';
import {FlowType} from '../../../model/flow-type.enum';
import {Translate, useTranslate} from '../../../shared/translate';
import {useWeeklyErrorAudit} from '../../hooks/api/use-weekly-error-audit';
import useConnectionSelect from '../../hooks/useConnectionSelect';
import {WeeklyAuditChart} from '../Chart/WeeklyAuditChart';
import {ConnectionSelect} from '../ConnectionSelect';
import {NoConnection} from '../NoConnection';
import {SectionTitle} from 'akeneo-design-system';

export const DataSourceErrorChart = () => {
    const translate = useTranslate();

    const {weeklyErrorAuditData} = useWeeklyErrorAudit();
    const {connections, connectionCode, selectConnectionCode} = useConnectionSelect(FlowType.DATA_SOURCE);

    if (0 === connections.filter(connection => connection.code !== '<all>').length) {
        return (
            <Container>
                <SectionTitle>
                    <SectionTitle.Title>
                        {translate(
                            'akeneo_connectivity.connection.dashboard.error_management.data_source_error_chart.section.title'
                        )}
                    </SectionTitle.Title>
                </SectionTitle>
                <NoConnectionContainer>
                    <NoConnection small flowType={FlowType.DATA_SOURCE} />
                </NoConnectionContainer>
            </Container>
        );
    }

    return (
        <Container>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate(
                        'akeneo_connectivity.connection.dashboard.error_management.data_source_error_chart.section.title'
                    )}
                </SectionTitle.Title>
                <SectionTitle.Spacer />
                <ConnectionSelect
                    connections={connections}
                    onChange={code => selectConnectionCode(code!)}
                    label={translate('akeneo_connectivity.connection.dashboard.connection_selector.title.source')}
                />
            </SectionTitle>
            <SmallHelper>
                <Translate id='akeneo_connectivity.connection.dashboard.error_management.data_source_error_chart.section.helper' />
            </SmallHelper>
            {weeklyErrorAuditData[connectionCode] ? (
                <WeeklyAuditChart
                    theme='red'
                    title={translate(
                        'akeneo_connectivity.connection.dashboard.error_management.data_source_error_chart.chart.title'
                    )}
                    weeklyAuditData={weeklyErrorAuditData[connectionCode]}
                />
            ) : (
                <Loading />
            )}
        </Container>
    );
};

const Container = styled.div`
    padding-bottom: 25px;
    display: block;
`;
const NoConnectionContainer = styled.div`
    border: 1px solid ${({theme}) => theme.color.grey60};
    padding-bottom: 20px;
    margin-top: 20px;
`;
