import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
const requireContext = require('require-context');
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

export interface EditTabsConfiguration {
  tabs: {
    [code: string]: {
      label: string;
      panel: string;
    };
  };
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
    let tabs: Tab[] = [];
    for (let code in this.configuration.tabs) {
      let tab = this.configuration.tabs[code];
      tabs = [...tabs, {code, label: __(tab.label)}];
    }

    return tabs;
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
