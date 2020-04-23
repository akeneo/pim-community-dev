import React from 'react';
import { ApplicationDependenciesProvider } from '../dependenciesTools';
import ReactController from '../dependenciesTools/reactController/ReactController';
import { CreateRules as CreateRulesPage } from '../pages/CreateRules';

class CreateRules extends ReactController {
  reactElementToMount() {
    return (
      <ApplicationDependenciesProvider>
        <CreateRulesPage />
      </ApplicationDependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /^pimee_catalog_rule_create/;
  }

  initialize() {
    return super.initialize();
  }

  renderRoute() {
    return super.renderRoute();
  }
}

export = CreateRules;
