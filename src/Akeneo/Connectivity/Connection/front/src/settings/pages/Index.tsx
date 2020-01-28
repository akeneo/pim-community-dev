import React from 'react';
import {Route, Switch} from 'react-router-dom';
import {ConnectionsProvider} from '../connections-context';
import {CreateConnection} from './CreateConnection';
import {DeleteConnection} from './DeleteConnection';
import {EditConnection} from './EditConnection';
import {ListConnections} from './ListConnections';
import {RegenerateConnectionPassword} from './RegenerateConnectionPassword';
import {RegenerateConnectionSecret} from './RegenerateConnectionSecret';
import {SettingsErrorBoundary} from './SettingsErrorBoundary';

export const Index = () => (
    <SettingsErrorBoundary>
        <ConnectionsProvider>
            <Switch>
                <Route path='/connections/:code/edit'>
                    <EditConnection />
                </Route>
                <Route path='/connections/:code/regenerate-secret'>
                    <RegenerateConnectionSecret />
                </Route>
                <Route path='/connections/:code/regenerate-password'>
                    <RegenerateConnectionPassword />
                </Route>
                <Route path='/connections/:code/delete'>
                    <DeleteConnection />
                </Route>
                <Route path='/connections/create'>
                    <CreateConnection />
                </Route>
                <Route path='/connections'>
                    <ListConnections />
                </Route>
            </Switch>
        </ConnectionsProvider>
    </SettingsErrorBoundary>
);
