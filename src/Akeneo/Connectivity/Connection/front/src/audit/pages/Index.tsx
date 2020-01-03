import React from 'react';
import {DashboardProvider} from '../dashboard-context';
import {Dashboard} from './Dashboard';

export const Index = () => (
    <DashboardProvider>
        <Dashboard />
    </DashboardProvider>
);
