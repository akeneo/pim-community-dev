const fetcherRegistry = require('pimenrich/js/fetcher/fetcher-registry');
const requireContext = require('require-context');
import FilterInterface, {
  AttributeFilter,
  PropertyFilter,
  NormalizedFilter,
  BaseFilter
} from 'pimfront/product-grid/domain/model/filter/filter';
import {Field, Property, Attribute} from 'pimfront/product-grid/domain/model/field';
import {BaseOperator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value} from 'pimfront/product-grid/domain/model/filter/value';
import {BaseOperator as Operator} from 'pimfront/product-grid/domain/model/filter/operator';

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
  value: string[]
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

  public async getEmptyFilter(code: string): Promise<FilterInterface> {
    const FilterClass = await this.getFilter(code);

    return FilterClass.createEmpty(await this.getField(code));
  }

  public async getPopulatedFilter(filter: NormalizedFilter): Promise<FilterInterface> {
    const FilterClass = await this.getFilter(filter.field);

    const field = await this.getField(filter.field);
    const operator = await this.getOperator(filter.operator);
    const value = await this.getValue(filter.value);

    return FilterClass.create(field, operator, value);
  }

  private async getFilter(code: string): Promise<typeof BaseFilter> {
    const isProperty = Object.keys(this.configuration.property).includes(code);

    return isProperty ?
      this.getPropertyFilter(code) :
      this.getAttributeFilter(code);
  }

  /**
   * Loads a property filter for the given code
   */
  private async getPropertyFilter(code: string): Promise<typeof PropertyFilter> {
    return await this.loadModule(
      this.configuration.property[code].model
    );
  }

  /**
   * Loads and create an attribute filter for the given code
   */
  private async getAttributeFilter(code: string): Promise<typeof AttributeFilter> {
    const rawAttribute = await this.fetcherRegistry.getFetcher('attribute').fetch(code);

    const attribute = Attribute.createFromAttribute(rawAttribute);

    return await this.loadModule(
      this.configuration.attribute[attribute.type].model
    );
  }

  private async getField(code: string): Promise<Field> {
    const isProperty = Object.keys(this.configuration.property).includes(code);

    if (isProperty) {
      return Promise.resolve(
        Property.createFromProperty({identifier: code, label: this.configuration.property[code].label})
      );
    }

    const rawAttribute = await this.fetcherRegistry.getFetcher('attribute').fetch(code);

    return Attribute.createFromAttribute(rawAttribute);
  }

  private async getOperator(operator: string): Promise<Operator>{
    const operatorPath = this.configuration.operator[operator];

    if (undefined === operatorPath) {
      throw Error(`The operator "${operator}" isn't defined.
Did you register well the operator in your configuration?`)
    }

    const OperatorClass: typeof Operator = await this.loadModule(operatorPath);

    if (!(OperatorClass instanceof BaseOperator)) {
      throw Error(`The given module (${operatorPath}) doesn't implement OperatorInterface.`)
    }

    return OperatorClass.create();
  }

  private async getValue(value: any): Promise<Value>{
    const valueFactories = await Promise.all(
      this.configuration.value.map(async (valueFactoryPath: string) => this.loadModule(valueFactoryPath))
    );

    const valueFactory = valueFactories.find((valueFactory: any) => null !== valueFactory(value));

    if (undefined === valueFactory) {
      throw Error(`Cannot find a value factory for value ${JSON.stringify(value)}.
Did you register well the value factory in your configuration?`)
    }

    return valueFactory(value);
  }

  private async loadModule(path: string): Promise<any> {
    const module = await this.requireContext(`${path}`);

    if (typeof module === 'undefined') {
      throw new Error(`The module "${path}" does not exists. You may have an error in your filter configuration file.`);
    }

    return module.default;
  }
}

export default Filters.create(__moduleConfig as FilterConfiguration, fetcherRegistry, requireContext);
