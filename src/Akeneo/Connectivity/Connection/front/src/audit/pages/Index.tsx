import React from 'react';
import {DashboardProvider} from '../dashboard-context';
import {AuditErrorBoundary} from './AuditErrorBoundary';
import {Dashboard} from './Dashboard';

export const Index = () => (
    <AuditErrorBoundary>
        <DashboardProvider>
            <Dashboard />
        </DashboardProvider>
    </AuditErrorBoundary>
);
