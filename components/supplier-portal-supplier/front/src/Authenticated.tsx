import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {ProductFileDropping} from './pages';

const Authenticated = () => {
    return (
        <Switch>
            <Route path="/">
                <ProductFileDropping />
            </Route>
        </Switch>
    );
};

export {Authenticated};
