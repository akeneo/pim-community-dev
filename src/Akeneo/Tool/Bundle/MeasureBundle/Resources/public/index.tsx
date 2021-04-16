import React from 'react';
import {ThemeProvider} from 'styled-components';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {List} from 'akeneomeasure/pages/list';
import {Edit} from 'akeneomeasure/pages/edit';
import {ConfigContext, ConfigContextValue} from 'akeneomeasure/context/config-context';
import {UnsavedChangesContext, UnsavedChangesContextValue} from 'akeneomeasure/context/unsaved-changes-context';
import {DependenciesContextProps} from '@akeneo-pim-community/legacy-bridge';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';

type IndexProps = DependenciesContextProps & {
  dependencies: {
    config: ConfigContextValue;
    unsavedChanges: UnsavedChangesContextValue;
  };
};

const Index = ({dependencies}: IndexProps) => (
  <DependenciesProvider>
    <ConfigContext.Provider value={dependencies.config}>
      <UnsavedChangesContext.Provider value={dependencies.unsavedChanges}>
        <ThemeProvider theme={pimTheme}>
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
        </ThemeProvider>
      </UnsavedChangesContext.Provider>
    </ConfigContext.Provider>
  </DependenciesProvider>
);

export {Index};
