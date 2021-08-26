import React, {ElementType, ReactNode, StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {SelectUserProfilePage} from '../connect/pages/SelectUserProfilePage';
import {MarketplacePage} from '../connect/pages/MarketplacePage';
import {RouteDefinition, renderRoutes} from './routing';

export const MarketplaceRoutes: RouteDefinition[] = [
    {
        path: '/connect/marketplace/profile',
        component: SelectUserProfilePage,
    },
    {
        path: '/connect/marketplace',
        component: MarketplacePage
    }
];

type MarketplaceProps = {
    routes: RouteDefinition[];
};

export const Marketplace = withDependencies(({routes}: MarketplaceProps) => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    {renderRoutes(routes)}
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
