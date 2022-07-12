import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {FileDropping} from './pages';

const Authenticated = () => {
    return (
        <Switch>
            <Route path="/">
                <FileDropping />
            </Route>
        </Switch>
    );
};

export {Authenticated};
