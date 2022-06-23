import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {Login, SetUpPassword} from './pages';
import {routes} from './pages/routes';

const Authentication = () => {
    return (
        <Switch>
            <Route privateRoute={false} path={routes.setUpPassword}>
                <SetUpPassword />
            </Route>
            <Route privateRoute={false} path="/login">
                <Login />
            </Route>
        </Switch>
    );
};

export {Authentication};
