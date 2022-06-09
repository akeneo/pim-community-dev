import React from 'react';
import {useLocation} from 'react-router-dom';
import {Redirect, Route as ReactRoute, RouteProps as ReactRouteProps} from 'react-router';
import {useUserContext} from '../contexts';

type RouteProps = ReactRouteProps & {
    privateRoute?: boolean;
};

const loginPath = '/login';

const Route = ({privateRoute = true, ...props}: RouteProps) => {
    const location = useLocation();
    const {isAuthenticated} = useUserContext();

    if (privateRoute && !isAuthenticated && loginPath !== location.pathname) {
        return <Redirect to={`${loginPath}?origin=${location.pathname}${location.search}${location.hash}`} />;
    }
    return <ReactRoute {...props} />;
};

export {Route};
