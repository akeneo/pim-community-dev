import * as React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AppEdit} from './AppEdit';
import {AppList} from './AppList';

export const Index = () => (
    <Router>
        <Switch>
            <Route path='/apps/:code/edit'>
                <AppEdit />
            </Route>
            <Route path='/apps/create'>Create</Route>
            <Route path='/apps'>
                <AppList />
            </Route>
        </Switch>
    </Router>
);
