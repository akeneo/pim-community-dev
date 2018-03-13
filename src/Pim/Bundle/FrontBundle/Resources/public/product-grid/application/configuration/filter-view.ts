import * as React from 'react';
const requireContext = require('require-context');
import Filter, {AttributeFilter, PropertyFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import {Missconfiguration} from 'pimfront/product-grid/application/configuration/error';
import {InvalidArgument} from 'pimfront/product-grid/domain/model/error';

interface PropertyFilterConfiguration {
  [property: string]: {
    view: string;
  };
}

interface AttributeFilterConfiguration {
  [attribute: string]: {
    view: string;
  };
}

interface FilterConfiguration {
  property: PropertyFilterConfiguration;
  attribute: AttributeFilterConfiguration;
}

export class FilterProvider {
  private configuration: FilterConfiguration;
  private requireContext: any;

  private constructor(configuration: FilterConfiguration, requireContext: any) {
    this.configuration = configuration;
    this.requireContext = requireContext;
  }

  public static create(configuration: FilterConfiguration, requireContext: any): FilterProvider {
    return new FilterProvider(configuration, requireContext);
  }

  public async getFilter(model: Filter): Promise<typeof React.Component> {
    if (model instanceof PropertyFilter) {
      return await this.getPropertyFilter(model);
    }

    if (model instanceof AttributeFilter) {
      return await this.getAttributeFilter(model);
    }

    throw new InvalidArgument(
      `The given filter model (${model.normalize()}) is neither a PropertyFilter nor an AttributeFilter`
    );
  }

  private async getPropertyFilter(model: PropertyFilter): Promise<typeof React.Component> {
    const modelPathIsNotWellConfigured =
      undefined === this.configuration.property[model.field.identifier] ||
      undefined === this.configuration.property[model.field.identifier].view;

    if (modelPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
        pimfront/product-grid/application/configuration/filter-view:
            property:
                ${model.field.identifier}:
                    view: your_view_path_here
      `;

      throw new Missconfiguration(
        `Cannot load view configuration for property filter "${
          model.field.identifier
        }". The configuration path should be ${confPath}?`
      );
    }

    const viewModulePath = this.configuration.property[model.field.identifier].view;

    const View: typeof React.Component = await this.loadModule(viewModulePath);

    // if (!(View instanceof React.Component)) {
    //   throw new Missconfiguration(`The module "${viewModulePath}" is not a React component.`);
    // }

    return View;
  }

  private async getAttributeFilter(model: AttributeFilter): Promise<typeof React.Component> {
    const modelPathIsNotWellConfigured =
      undefined === this.configuration.attribute[model.field.type] ||
      undefined === this.configuration.attribute[model.field.type].view;

    if (modelPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
        pimfront/product-grid/application/configuration/filter-view:
            attribute:
                ${model.field.type}:
                    view: your_view_path_here
      `;

      throw new Missconfiguration(
        `Cannot load view configuration for attribute filter "${
          model.field.identifier
        }". The configuration path should be ${confPath}?`
      );
    }

    const viewModulePath = this.configuration.attribute[model.field.type].view;

    const View: typeof React.Component = await this.loadModule(viewModulePath);

    // if (!(View typeof React.Component)) {
    //   throw new Missconfiguration(`The module "${viewModulePath}" is not a React component.`);
    // }

    return View;
  }

  private async loadModule(path: string): Promise<any> {
    const module = await this.requireContext(`${path}`);

    if (typeof module === 'undefined') {
      throw new Missconfiguration(
        `The module "${path}" does not exists. You may have an error in your filter configuration file.`
      );
    }

    return module.default;
  }
}

export default FilterProvider.create(__moduleConfig as FilterConfiguration, requireContext);
