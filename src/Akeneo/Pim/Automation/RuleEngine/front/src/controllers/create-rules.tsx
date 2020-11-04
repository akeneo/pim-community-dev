import React from 'react';
import {ApplicationDependenciesProvider} from '../dependenciesTools';
import ReactController, {
  RouteParams,
} from '../dependenciesTools/reactController/ReactController';
import {CreateRules as CreateRulesPage} from '../pages/CreateRules';

class CreateRules extends ReactController {
  reactElementToMount(routeParams: RouteParams) {
    return (
      <ApplicationDependenciesProvider>
        <CreateRulesPage
          originalRuleCode={routeParams?.params?.originalRuleCode}
        />
      </ApplicationDependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /^pimee_catalog_rule_create/;
  }

  initialize() {
    return super.initialize();
  }

  renderRoute(routeParams: RouteParams) {
    return super.renderRoute(routeParams);
  }
}

export = CreateRules;
