const fetcherRegistry = require('pimenrich/js/fetcher/fetcher-registry');
const requireContext = require('require-context');
import FilterInterface, {
  AttributeFilter as AbstractAttributeFilter,
  PropertyFilter as AbstractPropertyFilter,
} from 'pimfront/product-grid/domain/model/filter/filter';
import {Property, Attribute, RawAttributeInterface} from 'pimfront/product-grid/domain/model/field';
import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';

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

interface OperatorFilterConfiguration {
  [identifier: string]: string;
}

interface FilterConfiguration {
  property: PropertyFilterConfiguration;
  attribute: AttributeFilterConfiguration;
  operator: OperatorFilterConfiguration;
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
  // public async getEmptyFilterModelsFromCodes(codes: string[]): Promise<FilterInterface[]> {
  //   const propertyFilterCodesToBuild = Object.keys(this.configuration.property);
  //
  //   const suspectedAttributeFilterCodesToBuild = codes.filter(
  //     (code: string) => !propertyFilterCodesToBuild.includes(code)
  //   );
  //
  //   const attributeModels = await this.fetcherRegistry
  //     .getFetcher('attribute')
  //     .fetchByIdentifiers(suspectedAttributeFilterCodesToBuild);
  //
  //   const attributeFilterCodesToBuild = attributeModels.map((attribute: RawAttributeInterface) => attribute.identifier);
  //
  //   const filters = codes.map((code: string) => {
  //     const isAPropertyFilter = propertyFilterCodesToBuild.includes(code);
  //     const isAnAttributeFilter = attributeFilterCodesToBuild.includes(code);
  //
  //     if (!isAPropertyFilter && !isAnAttributeFilter) {
  //       throw Error(
  //         `The field "${code}" is neither an attribute filter nor a property filter. Did you registered it well in the requirejs configuration?`
  //       );
  //     }
  //
  //     return isAPropertyFilter ? this.createEmptyPropertyFilterModel(code) : this.createEmptyAttributeFilterModel(code);
  //   });
  //
  //   return Promise.all(filters);
  // }

  public async getEmptyFilterModelFromCode(code: string): Promise<FilterInterface> {
    const isProperty = Object.keys(this.configuration.property).includes(code);

    return isProperty ?
      this.createEmptyPropertyFilterModel(code) :
      this.createEmptyAttributeFilterModel(code);
  }

  public async getOperatorModel(operator: string):  Promise<OperatorInterface>{
    const operatorPath = this.configuration.operator[operator];

    if (undefined === operatorPath) {
      throw Error(`The operator "${operator}" isn't defined. Did you register well the operator in your configuration?`)
    }

    const Operator = this.loadModule(operatorPath).default;

    if (Operator instanceof BaseOperator) {
      throw Error(`The given module (${operatorPath}) doesn't implement OperatorInterface.`)
    }

    return Operator.create();
  }

/*  public async getViewFromFilterModel(filter: FilterInterface): Promise<any> {
    return filter instanceof AbstractPropertyFilter
      ? this.gePropertyViewFilterFromModel(filter)
      : this.getAttributeViewFilterFromModel(filter);
  }*/

  /**
   * Loads and create a property filter for the given code
   */
  private async createEmptyPropertyFilterModel(code: string): Promise<FilterInterface> {
    const PropertyFilter: typeof AbstractPropertyFilter = await this.loadModule(
      this.configuration.property[code].model
    );

    const property = Property.createFromProperty({identifier: code, label: this.configuration.property[code].label});

    return PropertyFilter.createEmptyFromProperty(property);
  }

  /**
   * Loads and create an attribute filter for the given code
   */
  private async createEmptyAttributeFilterModel(code: string): Promise<FilterInterface> {
    const rawAttribute = await this.fetcherRegistry.getFetcher('attribute').fetch(code);

    const attribute = Attribute.createFromAttribute(rawAttribute);

    const AttributeFilter: typeof AbstractAttributeFilter = await this.loadModule(
      this.configuration.attribute[attribute.type].model
    );

    return AttributeFilter.createEmptyFromAttribute(attribute);
  }

  /**
   * Loads and create a property filter for the given code
   */
  // private async getAttributeViewFilterFromModel(filter: AttributeFilterInterface): Promise<any> {
  //   this.loadModule(filter.field);
  //   return PropertyFilter.createEmptyFromProperty(property);
  // }

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
