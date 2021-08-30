import React, {ElementType, ReactNode} from 'react';
import {Route} from 'react-router-dom';

export type RouteDefinition = {
    path: string;
    component: ElementType;
};

export const renderRoutes = (routes: RouteDefinition[]): ReactNode => {
    return routes.map((route, i) => (
        <Route key={i} path={route.path} render={props => (
            <route.component {...props}/>
        )}/>
    ));
}

export const mergeRoutes = (routes: RouteDefinition[], overrides: RouteDefinition[]): RouteDefinition[] => {
    // first, replace any route from "routes" by a route from "overrides" if the path is the same
    const result = routes.map(route => overrides.find(override => override.path === route.path) || route);

    // then, add all routes from "overrides" where the path was not defined in "routes"
    overrides.forEach(override => {
        if (undefined === routes.find(route => route.path === override.path)) {
            result.push(override);
        }
    });

    return result;
};
