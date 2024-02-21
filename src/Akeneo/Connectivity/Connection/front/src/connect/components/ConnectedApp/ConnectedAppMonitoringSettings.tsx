import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, SectionTitle} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../shared/translate';
import {FlowType} from '../../../model/flow-type.enum';
import {FlowTypeSelect} from '../../../settings/components/FlowTypeSelect';
import {Checkbox} from '../../../common';
import {AuditableHelper} from '../../../settings/components/AuditableHelper';
import {FlowTypeHelper} from '../../../settings/components/FlowTypeHelper';
import {MonitoringSettings} from '../../../model/Apps/monitoring-settings';

const isAuditForbidden = (flowType: FlowType) => flowType === FlowType.OTHER;

const MonitoringField = styled(Field)`
    margin: 20px 0;
`;

type Props = {
    monitoringSettings: MonitoringSettings | null;
    handleSetMonitoringSettings: (monitoringSettings: MonitoringSettings) => void;
};

export const ConnectedAppMonitoringSettings: FC<Props> = ({monitoringSettings, handleSetMonitoringSettings}) => {
    const translate = useTranslate();

    if (null === monitoringSettings) {
        return null;
    }

    const onFlowTypeChange = (newFlowType: FlowType) => {
        handleSetMonitoringSettings({
            flowType: newFlowType,
            auditable: isAuditForbidden(newFlowType) ? false : monitoringSettings.auditable,
        });
    };

    const onAuditableChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        handleSetMonitoringSettings({
            flowType: monitoringSettings.flowType,
            auditable: event.target.checked,
        });
    };

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.monitoring.title')}
                </SectionTitle.Title>
            </SectionTitle>
            <MonitoringField label={translate('akeneo_connectivity.connection.connection.flow_type')}>
                <FlowTypeSelect value={monitoringSettings.flowType} onChange={onFlowTypeChange} id='flow_type' />
                <Helper inline level='info'>
                    {' '}
                    <FlowTypeHelper />{' '}
                </Helper>
            </MonitoringField>
            <MonitoringField label={''}>
                <Checkbox
                    name='auditable'
                    checked={monitoringSettings.auditable}
                    onChange={onAuditableChange}
                    disabled={isAuditForbidden(monitoringSettings.flowType)}
                    data-testid={'auditable-checkbox'}
                >
                    <Translate id='akeneo_connectivity.connection.connection.auditable' />
                </Checkbox>
                {isAuditForbidden(monitoringSettings.flowType) && (
                    <Helper inline level='info'>
                        {' '}
                        <AuditableHelper />{' '}
                    </Helper>
                )}
            </MonitoringField>
        </>
    );
};
