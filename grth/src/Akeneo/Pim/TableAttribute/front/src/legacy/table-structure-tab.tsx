// eslint-disable-next-line @typescript-eslint/no-var-requires
import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {TableStructureApp} from '../attribute';
import {
  AttributeType,
  getTranslatedTableConfigurationFromVariationTemplate,
  TableAttribute,
  TableConfiguration,
} from '../models';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Locale} from '@akeneo-pim-community/settings-ui';
import {LocaleCode} from '@akeneo-pim-community/shared';
import {SelectOptionRepository} from '../repositories';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const FetcherRegistry = require('pim/fetcher-registry');

type TableStructureTabConfig = {
  label: string;
  activeForTypes: AttributeType[];
};

type Violation = {global: boolean; message: string; path: string};

class TableStructureTab extends (BaseView as {new (options: {config: TableStructureTabConfig}): any}) {
  private config: TableStructureTabConfig;
  private violations: Violation[];
  private savedColumnCodes: string[] | undefined;

  initialize(options: {config: TableStructureTabConfig}): void {
    this.config = options.config;
    this.violations = [];
    this.savedColumnCodes = undefined;
    BaseView.prototype.initialize.apply(this, options);
  }

  configure(): JQueryPromise<any> {
    if (this.isActive()) {
      this.trigger('tab:register', {
        code: this.code,
        label: translate(this.config.label),
      });

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onBadRequest.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.removeErrors.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_save', this.resetSavedColumns.bind(this));
    }

    const templateVariationCode = this.getQueryParam('template_variation');
    if (templateVariationCode) {
      this.getTableConfigurationFromTemplate(templateVariationCode).then(tableConfiguration => {
        if (tableConfiguration.length > 0) {
          this.handleChange(tableConfiguration);
        }
      });
    }

    return super.configure();
  }

  removeErrors(): void {
    if (this.violations.length) {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
    }
    this.violations = [];
  }

  resetSavedColumns(): void {
    SelectOptionRepository.clearCache();
    this.savedColumnCodes = undefined;
  }

  onBadRequest(event: {response: Violation[]}): void {
    this.violations = event.response;

    /** Possible paths:
     * - 'table_configuration' (error on column count)
     * - 'raw_table_configuration' (error on duplicate code)
     * - 'raw_table_configuration[2][validations]' (error on a validation field)
     */
    const isATableConfigurationViolation: (path: string) => boolean = path => {
      if (path === 'table_configuration') return true;
      if (path === 'raw_table_configuration') return true;
      if (/^raw_table_configuration/.exec(path)) return true;

      return false;
    };

    const errors = event.response.filter(violation => isATableConfigurationViolation(violation.path));
    if (errors.length) {
      this.getRoot().trigger('pim_enrich:form:form-tabs:add-errors', {
        tabCode: this.code,
        errors,
      });
      this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.code);
    }
  }

  handleChange(tableConfiguration: TableConfiguration): void {
    const data = this.getFormData();
    data.table_configuration = tableConfiguration;
    this.setData({...data});
  }

  getQueryParam(paramName: string): any {
    const urlString = window.location.href;
    const index = urlString.indexOf('?');
    if (index < 0) {
      return null;
    }
    const params = new URLSearchParams(urlString.substring(index + 1));

    return params.get(paramName);
  }

  render(): any {
    if (!this.isActive()) {
      return;
    }

    const initialTableConfiguration = this.getFormData().table_configuration as TableConfiguration | undefined;
    if (typeof this.savedColumnCodes === 'undefined') {
      this.savedColumnCodes = (initialTableConfiguration || []).map(columnDefinition => columnDefinition.code);
    }
    const attribute: TableAttribute = this.getFormData();

    this.getTableConfiguration(initialTableConfiguration).then(tableConfiguration => {
      this.handleChange(tableConfiguration);

      ReactDOM.render(
        <DependenciesProvider>
          <TableStructureApp
            attribute={attribute}
            initialTableConfiguration={tableConfiguration}
            onChange={this.handleChange.bind(this)}
            savedColumnCodes={this.savedColumnCodes || []}
          />
        </DependenciesProvider>,
        this.el
      );
    });

    return this;
  }

  remove(): any {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private async getTableConfiguration(
    initialTableConfiguration: TableConfiguration | undefined
  ): Promise<TableConfiguration> {
    if (typeof initialTableConfiguration !== 'undefined') {
      return new Promise(resolve => resolve(initialTableConfiguration));
    }

    const templateVariationCode = this.getQueryParam('template_variation');
    if (templateVariationCode) {
      return this.getTableConfigurationFromTemplate(templateVariationCode);
    }

    return new Promise(resolve => resolve([]));
  }

  private async getTableConfigurationFromTemplate(templateVariationCode: string): Promise<TableConfiguration> {
    /**
     * Only the locales which are in the catalog locales list (i.e. activated) AND available in the UI (i.e.
     * translated) can have translated template data.
     */
    const activatedLocales = await FetcherRegistry.getFetcher('locale').fetchActivated();
    const activatedLocaleCodes = activatedLocales.map((locale: Locale) => locale.code);
    const uiLocales = await FetcherRegistry.getFetcher('ui-locale').fetchAll();
    const uiLocaleCodes = uiLocales.map((locale: Locale) => locale.code);
    const activatedUiLocales = activatedLocaleCodes.filter((localeCode: LocaleCode) =>
      uiLocaleCodes.includes(localeCode)
    );

    return getTranslatedTableConfigurationFromVariationTemplate(templateVariationCode, activatedUiLocales);
  }

  private isActive() {
    return this.config.activeForTypes.includes(this.getRoot().getType());
  }
}

export = TableStructureTab;
