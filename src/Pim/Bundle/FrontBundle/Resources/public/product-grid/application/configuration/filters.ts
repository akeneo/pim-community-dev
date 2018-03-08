const fetcherRegistry = require('pimenrich/js/fetcher/fetcher-registry');
const requireContext = require('require-context');
import FilterInterface, {
  AttributeFilter as AbstractAttributeFilter,
  PropertyFilter as AbstractPropertyFilter,
} from 'pimfront/product-grid/domain/model/filter/filter';
import {Property, Attribute, RawAttributeInterface} from 'pimfront/product-grid/domain/model/field';

interface PropertyFilterConfiguration {
  [property: string]: {
    model: string;
    label: string;
  };
}

interface AttributeFilterConfiguration {
  [attribute: string]: {
    model: string;
  };
}

interface FilterConfiguration {
  property: PropertyFilterConfiguration;
  attribute: AttributeFilterConfiguration;
}

export class Filters {
  private configuration: FilterConfiguration;
  private fetcherRegistry: any;
  private requireContext: any;

  private constructor(configuration: FilterConfiguration, fetcherRegistry: any, requireContext: any) {
    this.configuration = configuration;
    this.fetcherRegistry = fetcherRegistry;
    this.requireContext = requireContext;
  }

  public static create(configuration: FilterConfiguration, fetcherRegistry: any, requireContext: any): Filters {
    return new Filters(configuration, fetcherRegistry, requireContext);
  }

  /**
   * Provides the filter models from the code given in parameter
   *
   * There is two types of filters:
   *  - property filters (status, completeness, etc)
   *  - attribute filters (sku, name, description, etc)
   *
   * To determine which one is which from the code, we look for the property field configuration and fetche the attributes
   */
  public async getEmptyFiltersFromCodes(codes: string[]): Promise<FilterInterface[]> {
    const propertyFilterCodesToBuild = Object.keys(this.configuration.property);

    const suspectedAttributeFilterCodesToBuild = codes.filter(
      (code: string) => !propertyFilterCodesToBuild.includes(code)
    );

    // We prefetch attributes to have them in cache with only one request.
    const attributeModels = await this.fetcherRegistry
      .getFetcher('attribute')
      .fetchByIdentifiers(suspectedAttributeFilterCodesToBuild);

    const attributeFilterCodesToBuild = attributeModels.map((attribute: RawAttributeInterface) => attribute.identifier);

    const filters = codes.map((code: string) => {
      const isAPropertyFilter = propertyFilterCodesToBuild.includes(code);
      const isAnAttributeFilter = attributeFilterCodesToBuild.includes(code);

      if (!isAPropertyFilter && !isAnAttributeFilter) {
        throw Error(
          `The field "${code}" is neither an attribute filter nor a property filter. Did you registered it well in the requirejs configuration?`
        );
      }

      return isAPropertyFilter ? this.createEmptyPropertyFilter(code) : this.createEmptyAttributeFilter(code);
    });

    return Promise.all(filters);
  }

  /**
   * Loads and create a property filter for the given code
   */
  private async createEmptyPropertyFilter(code: string): Promise<FilterInterface> {
    const PropertyFilter: typeof AbstractPropertyFilter = await this.loadModule(
      this.configuration.property[code].model
    );

    const property = Property.createFromProperty({identifier: code, label: this.configuration.property[code].label});

    return PropertyFilter.createEmptyFromProperty(property);
  }

  /**
   * Loads and create an attribute filter for the given code
   */
  private async createEmptyAttributeFilter(code: string): Promise<FilterInterface> {
    const rawAttribute = await this.fetcherRegistry.getFetcher('attribute').fetch(code);

    const attribute = Attribute.createFromAttribute(rawAttribute);

    const AttributeFilter: typeof AbstractAttributeFilter = await this.loadModule(
      this.configuration.attribute[attribute.type].model
    );

    return AttributeFilter.createEmptyFromAttribute(attribute);
  }

  private async loadModule(path: string): Promise<any> {
    console.log(path);
    const module = await this.requireContext(`${path}`);

    if (typeof module === 'undefined') {
      throw new Error(`The module "${path}" does not exists. You may have an error in your filter configuration file.`);
    }

    return module.default;
  }
}

export default Filters.create(__moduleConfig as FilterConfiguration, fetcherRegistry, requireContext);
