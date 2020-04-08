import React from "react";
import { ApplicationDependenciesProvider } from "../dependenciesTools";
import ReactController, {RouteParams} from "../dependenciesTools/reactController/ReactController";
import { EditRules as EditRulesPage } from "../pages/EditRules";

class EditRules extends ReactController {
  reactElementToMount(routeParams: RouteParams) {
    return (
      <ApplicationDependenciesProvider>
        <EditRulesPage ruleDefinitionCode={routeParams.params.code}/>
      </ApplicationDependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /^pimee_catalog_rule_edit/;
  }

  initialize() {
    return super.initialize();
  }

  renderRoute(routeParams: any) {
    return super.renderRoute(routeParams);
  }
}

export = EditRules;
