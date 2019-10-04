import * as React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {EditApp} from './EditApp';
import {ListApp} from './ListApp';

export const Index = () => (
    <Router>
        <Switch>
            <Route path='/apps/:id'>
                <EditApp />
            </Route>
            <Route path='/apps'>
                <ListApp />
            </Route>
        </Switch>
    </Router>
);
