import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
const requireContext = require('require-context');
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

interface EditTabConfiguration {
  [code: string]: {
    label: string;
    panel: string;
  };
}

interface EditTabsConfiguration {
  tabs: EditTabConfiguration;
  current_tab: string;
}

export class EditTabsProvider {
  private configuration: EditTabsConfiguration;
  private requireContext: any;

  private constructor(configuration: EditTabsConfiguration, requireContext: any) {
    this.configuration = configuration;
    this.requireContext = requireContext;
  }

  public static create(configuration: EditTabsConfiguration, requireContext: any): EditTabsProvider {
    return new EditTabsProvider(configuration, requireContext);
  }

  public getTabs(): Tab[] {
    return Object.keys(this.configuration.tabs).map((code: string) => {
      return {code, label: __(this.configuration.tabs[code].label)};
    });
  }

  public getCurrentTab(): string {
    return this.configuration.current_tab;
  }

  public async getView(code: string): Promise<typeof React.Component> {
    const viewModulePath = this.configuration.tabs[code].panel;

    const View: typeof React.Component = await this.loadModule(viewModulePath);

    return View;
  }

  private async loadModule(path: string): Promise<any> {
    const module = await this.requireContext(`${path}`);

    return module.default;
  }
}

export default EditTabsProvider.create(__moduleConfig as EditTabsConfiguration, requireContext);
