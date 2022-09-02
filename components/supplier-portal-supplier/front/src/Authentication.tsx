import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {Login, ResetPassword, SetUpPassword} from './pages';
import {routes} from './pages/routes';

const Authentication = () => {
    return (
        <Switch>
            <Route privateRoute={false} path={routes.setUpPassword}>
                <SetUpPassword />
            </Route>
            <Route privateRoute={false} path={routes.login}>
                <Login />
            </Route>
            <Route privateRoute={false} path={routes.resetPassword}>
                <ResetPassword />
            </Route>
        </Switch>
    );
};

export {Authentication};
