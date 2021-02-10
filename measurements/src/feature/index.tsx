import React, {useEffect} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {List} from './pages/list';
import {Edit} from './pages/edit';
import {ConfigContext} from './context/config-context';
import {UnsavedChangesContext} from './context/unsaved-changes-context';
import {useDependenciesContext} from '@akeneo-pim-community/legacy';

// TODO: we should remove this and provide proper dependencies provider for config and unsavedChanges

const value = {
  hasUnsavedChanges: false,
  setHasUnsavedChanges: (newValue: boolean) => {
    value.hasUnsavedChanges = newValue;
  },
};

const Index = () => {
  const {mediator} = useDependenciesContext();

  useEffect(() => {
    if (mediator) {
      mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
      mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-measurements-settings'});
    }
  }, [mediator]);

  return (
    <ConfigContext.Provider value={{operations_max: 5, units_max: 50, families_max: 300}}>
      <UnsavedChangesContext.Provider value={value}>
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
};

export default Index;
