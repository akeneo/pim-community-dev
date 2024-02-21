import React from 'react';
import {useDashboardState} from '../dashboard-context';
import {DataDestinationCharts} from './DataDestinationCharts';
import {DataSourceCharts} from './DataSourceCharts';
import {BusinessErrorCountWidget} from './ErrorManagement/BusinessErrorCountWidget';
import {DataSourceErrorChart} from './ErrorManagement/DataSourceErrorChart';
import {NoConnection} from './NoConnection';
import {UserSurvey} from './UserSurvey';

export const DashboardContent = () => {
    const state = useDashboardState();

    if (0 === Object.values(state.connections).length) {
        return <NoConnection />;
    }

    return (
        <>
            <DataSourceCharts />
            <DataDestinationCharts />
            <DataSourceErrorChart />
            <BusinessErrorCountWidget />
            <UserSurvey />
        </>
    );
};
