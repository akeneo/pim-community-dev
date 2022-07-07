import React from 'react';
import {useLocation} from 'react-router-dom';
import {Redirect, Route as ReactRoute, RouteProps as ReactRouteProps} from 'react-router';
import {useUserContext} from '../contexts';
import {routes} from '../pages/routes';

type RouteProps = ReactRouteProps & {
    privateRoute?: boolean;
};

const Route = ({privateRoute = true, ...props}: RouteProps) => {
    const location = useLocation();
    const {isAuthenticated} = useUserContext();

    if (privateRoute && !isAuthenticated && routes.login !== location.pathname) {
        return <Redirect to={`${routes.login}?origin=${location.pathname}${location.search}${location.hash}`} />;
    }
    return <ReactRoute {...props} />;
};

export {Route};
