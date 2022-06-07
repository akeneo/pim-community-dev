import React from 'react';
import { useLocation } from 'react-router-dom';
import { Route as ReactRoute, RouteProps as ReactRouteProps, Redirect } from 'react-router';
import {useUserContext} from "../contexts";

type RouteProps = {
    privateRoute?: boolean;
};

const loginPath = '/login';

const Route: React.FC<RouteProps & ReactRouteProps> = ( { privateRoute = true, ...props } ) => {
    const location = useLocation();
    const {isAuthenticated} = useUserContext();

    if ( privateRoute && !isAuthenticated && loginPath !== location.pathname ) {
        return <Redirect to={`${loginPath}?origin=${location.pathname}${location.search}${location.hash}`} />;
    }
    return <ReactRoute {...props} />;
};

export {Route};
