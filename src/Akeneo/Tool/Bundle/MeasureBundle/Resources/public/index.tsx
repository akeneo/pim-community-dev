import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {List} from 'akeneomeasure/pages/list';
import {Edit} from 'akeneomeasure/pages/edit';
import {ConfigContext} from 'akeneomeasure/context/config-context';
import {UnsavedChangesContext} from 'akeneomeasure/context/unsaved-changes-context';

// TODO: we should remove this and provide proper dependencies provider for config and unsavedChanges
import {measurementsDependencies} from 'akeneomeasure/bridge/dependencies';

const Index = () => (
  <ConfigContext.Provider value={measurementsDependencies.config}>
    <UnsavedChangesContext.Provider value={measurementsDependencies.unsavedChanges}>
      <Router basename="/configuration/measurement">
        <Switch>
          <Route path="/:measurementFamilyCode">
            <Edit />
          </Route>
          <Route path="/">
            <List />
          </Route>
        </Switch>
      </Router>
    </UnsavedChangesContext.Provider>
  </ConfigContext.Provider>
);

export {Index};
