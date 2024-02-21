import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders} from '../../../../test-utils';
import {ConnectedAppMonitoringSettings} from '@src/connect/components/ConnectedApp/ConnectedAppMonitoringSettings';
import {FlowType} from '@src/model/flow-type.enum';
import {act, screen, waitFor} from '@testing-library/react';
import {AuditableHelper} from '@src/settings/components/AuditableHelper';
import {FlowTypeHelper} from '@src/settings/components/FlowTypeHelper';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

jest.mock('@src/settings/components/AuditableHelper', () => ({
    ...jest.requireActual('@src/settings/components/AuditableHelper'),
    AuditableHelper: jest.fn(() => null),
}));
jest.mock('@src/settings/components/FlowTypeHelper', () => ({
    ...jest.requireActual('@src/settings/components/AuditableHelper'),
    FlowTypeHelper: jest.fn(() => null),
}));

test('It renders the app monitoring settings form renders with flowType "other"', () => {
    renderWithProviders(
        <ConnectedAppMonitoringSettings
            monitoringSettings={{flowType: FlowType.OTHER, auditable: false}}
            handleSetMonitoringSettings={jest.fn()}
        />
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.monitoring.title')
    ).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connection.flow_type')).toBeInTheDocument();
    expect(FlowTypeHelper).toHaveBeenCalled();
    expect(screen.queryByText('akeneo_connectivity.connection.connection.auditable')).toBeInTheDocument();
    expect(AuditableHelper).toHaveBeenCalled();
    expect(screen.getByTestId('auditable-checkbox')).toHaveAttribute('disabled');
});

test('It disables the auditable checkbox when the flowType becomes "other"', () => {
    const handleSetMonitoring = jest.fn();

    renderWithProviders(
        <ConnectedAppMonitoringSettings
            monitoringSettings={{flowType: FlowType.DATA_SOURCE, auditable: true}}
            handleSetMonitoringSettings={handleSetMonitoring}
        />
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.monitoring.title')
    ).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connection.flow_type')).toBeInTheDocument();
    expect(FlowTypeHelper).toHaveBeenCalled();
    expect(screen.queryByText('akeneo_connectivity.connection.connection.auditable')).toBeInTheDocument();
    expect(AuditableHelper).not.toHaveBeenCalled();
    expect(screen.getByTestId('auditable-checkbox')).not.toHaveAttribute('disabled');

    const flowTypeSelect = screen.getByLabelText('akeneo_connectivity.connection.connection.flow_type');

    userEvent.click(flowTypeSelect);
    userEvent.click(screen.getByText('akeneo_connectivity.connection.flow_type.data_destination'));

    expect(handleSetMonitoring).toHaveBeenCalledWith({flowType: FlowType.DATA_DESTINATION, auditable: true});

    userEvent.click(flowTypeSelect);
    userEvent.click(screen.getByText('akeneo_connectivity.connection.flow_type.other'));

    expect(handleSetMonitoring).toHaveBeenCalledWith({flowType: FlowType.OTHER, auditable: false});
});

test('It can check the auditable checkbox if the flowType is not "other"', () => {
    const handleSetMonitoring = jest.fn();

    renderWithProviders(
        <ConnectedAppMonitoringSettings
            monitoringSettings={{flowType: FlowType.DATA_SOURCE, auditable: false}}
            handleSetMonitoringSettings={handleSetMonitoring}
        />
    );

    const auditableCheckbox = screen.getByTestId('auditable-checkbox');

    userEvent.click(auditableCheckbox);

    expect(handleSetMonitoring).toHaveBeenCalledWith({flowType: FlowType.DATA_SOURCE, auditable: true});
});
