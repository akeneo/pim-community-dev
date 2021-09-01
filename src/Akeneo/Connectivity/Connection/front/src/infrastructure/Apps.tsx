import React, {StrictMode} from 'react';
import {HashRouter as Router, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {AppActivatePage} from '../connect/pages/AppActivatePage';
import {AppAuthorizePage} from '../connect/pages/AppAuthorizePage';
import {renderRoutes, RouteDefinition} from './routing';

export const AppsRoutes: RouteDefinition[] = [
    {
        path: '/connect/apps/activate',
        component: AppActivatePage,
    },
    {
        path: '/connect/apps/authorize',
        component: AppAuthorizePage,
    },
];

type Props = {
    routes: RouteDefinition[];
};

export const Apps = withDependencies(({routes}: Props) => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>{renderRoutes(routes)}</Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
