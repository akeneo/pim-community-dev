import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {ProductFileDropping, ProductFileHistory} from './pages';
import {routes} from './pages/routes';

const Authenticated = () => {
    return (
        <Switch>
            <Route path={routes.productFileHistory}>
                <ProductFileHistory />
            </Route>
            <Route path={routes.filesDropping}>
                <ProductFileDropping />
            </Route>
        </Switch>
    );
};

export {Authenticated};
