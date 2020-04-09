import React from "react";
import { ApplicationDependenciesProvider } from "../dependenciesTools";
import ReactController from "../dependenciesTools/reactController/ReactController";
import { EditRules as EditRulesPage } from "../pages/EditRules";

class EditRules extends ReactController {
  reactElementToMount() {
    return (
      <ApplicationDependenciesProvider>
        <EditRulesPage />
      </ApplicationDependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /^pimee_catalog_rule_edit/;
  }

  initialize() {
    return super.initialize();
  }

  renderRoute() {
    return super.renderRoute();
  }
}

export = EditRules;
