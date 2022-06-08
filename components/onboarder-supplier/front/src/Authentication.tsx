import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {SetUpPassword} from './pages';

const Authentication = () => {
    return (
        <Switch>
            <Route privateRoute={false} path="/set-up-password">
                <SetUpPassword />
            </Route>
        </Switch>
    );
};

export {Authentication};
