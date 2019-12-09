import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AppCreate} from './AppCreate';
import {AppDelete} from './AppDelete';
import {AppEdit} from './AppEdit';
import {AppList} from './AppList';
import {AppRegenerateSecret} from './AppRegenerateSecret';

export const Index = () => (
    <Router>
        <Switch>
            <Route path='/apps/:code/edit'>
                <AppEdit />
            </Route>
            <Route path='/apps/:code/regenerate-secret'>
                <AppRegenerateSecret />
            </Route>
            <Route path='/apps/:code/delete'>
                <AppDelete />
            </Route>
            <Route path='/apps/create'>
                <AppCreate />
            </Route>
            <Route path='/apps'>
                <AppList />
            </Route>
        </Switch>
    </Router>
);
