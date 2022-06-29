import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {FilesDropping} from './pages';

const Authenticated = () => {
    return (
        <Switch>
            <Route path="/">
                <FilesDropping />
            </Route>
        </Switch>
    );
};

export {Authenticated};
