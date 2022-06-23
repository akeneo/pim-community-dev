import React from 'react';
import {Switch} from 'react-router-dom';
import {Route} from './components';
import {FileTransfer} from './pages';

const Authenticated = () => {
    return (
        <Switch>
            <Route path="/">
                <FileTransfer />
            </Route>
        </Switch>
    );
};

export {Authenticated};
