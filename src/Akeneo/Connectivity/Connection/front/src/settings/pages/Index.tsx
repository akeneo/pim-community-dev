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
import {WrongCredentialsCombinationsProvider} from '../wrong-credentials-combinations-context';

export const Index = () => (
    <SettingsErrorBoundary>
        <WrongCredentialsCombinationsProvider>
            <ConnectionsProvider>
                <Switch>
                    <Route path='/connect/connection-settings/:code/edit'>
                        <EditConnection />
                    </Route>
                    <Route path='/connect/connection-settings/:code/regenerate-secret'>
                        <RegenerateConnectionSecret />
                    </Route>
                    <Route path='/connect/connection-settings/:code/regenerate-password'>
                        <RegenerateConnectionPassword />
                    </Route>
                    <Route path='/connect/connection-settings/:code/delete'>
                        <DeleteConnection />
                    </Route>
                    <Route path='/connect/connection-settings/create'>
                        <CreateConnection />
                    </Route>
                    <Route path='/connect/connection-settings'>
                        <ListConnections />
                    </Route>
                </Switch>
            </ConnectionsProvider>
        </WrongCredentialsCombinationsProvider>
    </SettingsErrorBoundary>
);
