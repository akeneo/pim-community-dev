import React, {useEffect} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {List} from 'akeneomeasure/pages/list';
import {Edit} from 'akeneomeasure/pages/edit';
import {ConfigContext} from 'akeneomeasure/context/config-context';
import {UnsavedChangesContext} from 'akeneomeasure/context/unsaved-changes-context';

// TODO: we should remove this and provide proper dependencies provider for config and unsavedChanges
import {measurementsDependencies} from 'akeneomeasure/bridge/dependencies';
import {useDependenciesContext} from '@akeneo-pim-community/legacy-bridge';

const Index = () => {
  const {mediator} = useDependenciesContext();

  useEffect(() => {
    if (mediator) {
      mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
      mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-measurements-settings'});
    }
  }, [mediator]);

  return (
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
)};

export {Index};
