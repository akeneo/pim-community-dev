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

export const mergeRoutes = (a: RouteDefinition[], b: RouteDefinition[]): RouteDefinition[] => {
    // first, replace any route from A by a route from B if the path is the same
    let routes = a.map(ar => b.find(br => br.path === ar.path) || ar);

    // then, add all routes from B where the path was not defined in A
    b.forEach(br => {
        if (undefined === a.find(ar => ar.path === br.path)) {
            routes.push(br);
        }
    });

    return routes;
};
