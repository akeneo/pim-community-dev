import React from 'react';
import {ApplicationDependenciesProvider} from '../dependenciesTools';
import ReactController, {
  RouteParams,
} from '../dependenciesTools/reactController/ReactController';
import {EditRules as EditRulesPage} from '../pages/EditRules';
import {dependencies} from '../dependenciesTools/provider/dependencies';

class EditRules extends ReactController {
  private isDirty = false;

  reactElementToMount(routeParams: RouteParams) {
    return (
      <ApplicationDependenciesProvider>
        <EditRulesPage
          ruleDefinitionCode={routeParams.params.code}
          setIsDirty={this.setIsDirty.bind(this)}
        />
      </ApplicationDependenciesProvider>
    );
  }

  routeGuardToUnmount(): RegExp | false {
    return false;
  }

  initialize() {
    this.$el.addClass('AknRuleEngine-edit');

    return super.initialize();
  }

  renderRoute(routeParams: RouteParams) {
    dependencies.mediator.trigger('pim_menu:highlight:tab', {
      extension: 'pim-menu-settings',
    });
    dependencies.mediator.trigger('pim_menu:highlight:item', {
      extension: 'pim-menu-enrich-rule',
    });

    return super.renderRoute(routeParams);
  }

  setIsDirty(isDirty: boolean): void {
    this.isDirty = isDirty;
  }

  canLeave() {
    const message = dependencies.translate(
      'pimee_catalog_rule.form.edit.discard_changes'
    );

    return !this.isDirty || confirm(message);
  }
}

export = EditRules;
