import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {SetUpPassword} from './pages';
import {routes} from "./pages/routes";

const Authentication = () => {
    return (
        <Switch>
            <Route privateRoute={false} path={routes.setUpPassword}>
                <SetUpPassword />
            </Route>
        </Switch>
    );
};

export {Authentication};
