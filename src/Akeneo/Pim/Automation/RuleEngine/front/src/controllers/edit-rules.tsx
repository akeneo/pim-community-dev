import React from 'react';
import {ApplicationDependenciesProvider} from '../dependenciesTools';
import ReactController, {
  RouteParams,
} from '../dependenciesTools/reactController/ReactController';
import {EditRules as EditRulesPage} from '../pages/EditRules';
import {dependencies} from '../dependenciesTools/provider/dependencies';
import {AttributeValueConfig, ConfigContext} from '../context/ConfigContext';
import {
  CellInputsMapping,
  CellMatchersMapping,
} from '@akeneo-pim-ge/table_attribute';

class EditRules extends ReactController {
  private isDirty = false;

  reactElementToMount(routeParams: RouteParams) {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const attributeValueConfig = __moduleConfig.views as AttributeValueConfig;
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const cellInputsMapping = __moduleConfig.cell_inputs as CellInputsMapping;
    const cellMatchersMapping =
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      // @ts-ignore
      __moduleConfig.cell_matchers as CellMatchersMapping;

    return (
      <ApplicationDependenciesProvider>
        <ConfigContext.Provider
          value={{
            attributeValueConfig,
            cellInputsMapping,
            cellMatchersMapping,
          }}>
          <EditRulesPage
            ruleDefinitionCode={routeParams.params.code}
            setIsDirty={this.setIsDirty.bind(this)}
          />
        </ConfigContext.Provider>
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
