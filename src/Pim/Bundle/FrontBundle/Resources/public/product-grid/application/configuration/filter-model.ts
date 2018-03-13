const fetcherRegistry = require('pimenrich/js/fetcher/fetcher-registry');
const requireContext = require('require-context');
import Filter, {
  AttributeFilter,
  PropertyFilter,
  NormalizedFilter,
  BaseFilter,
} from 'pimfront/product-grid/domain/model/filter/filter';
import {Field, Property, Attribute, RawAttributeInterface} from 'pimfront/product-grid/domain/model/field';
import {Value} from 'pimfront/product-grid/domain/model/filter/value';
import {BaseOperator as Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import {InvalidArgument} from 'pimfront/product-grid/domain/model/error';
import {UnknownProperty, Missconfiguration} from 'pimfront/product-grid/application/configuration/error';

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
  value: string[];
}

export class FilterProvider {
  private configuration: FilterConfiguration;
  private fetcherRegistry: any;
  private requireContext: any;

  private constructor(configuration: FilterConfiguration, fetcherRegistry: any, requireContext: any) {
    this.configuration = configuration;
    this.fetcherRegistry = fetcherRegistry;
    this.requireContext = requireContext;
  }

  public static create(configuration: FilterConfiguration, fetcherRegistry: any, requireContext: any): FilterProvider {
    return new FilterProvider(configuration, fetcherRegistry, requireContext);
  }

  public async getEmptyFilter(code: string = ''): Promise<Filter> {
    if ('' === code) {
      throw new InvalidArgument('The method getEmptyFilter expect a code as parameter');
    }

    const FilterClass = await this.getFilter(code);

    return FilterClass.createEmpty(await this.getField(code));
  }

  public async getPopulatedFilter(filter: NormalizedFilter): Promise<Filter> {
    if (!(filter instanceof NormalizedFilter)) {
      throw new InvalidArgument('The method getPopulatedFilter expect a valid NormalizedFilter');
    }
    const FilterClass = await this.getFilter(filter.field);

    const field = await this.getField(filter.field);
    const operator = await this.getOperator(filter.operator);
    const value = await this.getValue(filter.value);

    return FilterClass.create(field, operator, value);
  }

  private async getFilter(code: string = ''): Promise<typeof BaseFilter> {
    const isProperty = Object.keys(this.configuration.property).includes(code);

    if (isProperty) {
      return this.getPropertyFilter(code);
    }

    let rawAttribute;
    try {
      rawAttribute = await this.fetcherRegistry.getFetcher('attribute').fetch(code);
    } catch (error) {
      throw new UnknownProperty(
        `The property "${code}" is neither an attribute filter nor a property filter. Did you registered it well in the requirejs configuration?`
      );
    }

    return this.getAttributeFilter(rawAttribute);
  }

  /**
   * Loads a property filter for the given code
   */
  private async getPropertyFilter(code: string): Promise<typeof PropertyFilter> {
    const modelPathIsNotWellConfigured =
      undefined === this.configuration.property[code] || undefined === this.configuration.property[code].model;

    if (modelPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
        pimfront/product-grid/application/configuration/filter-model:
            property:
                ${code}:
                    model: your_model_path_here
      `;

      throw new Missconfiguration(
        `Cannot load model configuration for property filter "${code}". The configuration path should be ${confPath}?`
      );
    }

    return await this.loadModule(this.configuration.property[code].model);
  }

  /**
   * Loads and create an attribute filter for the given code
   */
  private async getAttributeFilter(rawAttribute: RawAttributeInterface): Promise<typeof AttributeFilter> {
    const attribute = Attribute.createFromAttribute(rawAttribute);

    const modelPathIsNotWellConfigured =
      undefined === this.configuration.attribute[attribute.type] ||
      undefined === this.configuration.attribute[attribute.type].model;

    if (modelPathIsNotWellConfigured) {
      const confPath = `
  config:
      config:
          pimfront/product-grid/application/configuration/filter-model:
              attribute:
                  ${attribute.type}:
                      model: your_model_path_here
      `;

      throw new Missconfiguration(
        `Cannot load model configuration for attribute filter "${
          rawAttribute.identifier
        }". The configuration path should be ${confPath}?`
      );
    }

    return await this.loadModule(this.configuration.attribute[attribute.type].model);
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

  private async getOperator(operatorCode: string): Promise<Operator> {
    const operatorPath = this.configuration.operator[operatorCode];

    if (undefined === operatorPath) {
      throw new Missconfiguration(`The operator "${operatorCode}" isn't defined.
Did you register well the operator in your configuration?`);
    }

    const OperatorClass = await this.loadModule(operatorPath);

    const operator = OperatorClass.create();

    if (!(operator instanceof Operator)) {
      throw new Missconfiguration(`The given module "${operatorPath}" doesn't implement OperatorInterface.`);
    }

    return operator;
  }

  private async getValue(value: any): Promise<Value> {
    const valueFactories = await Promise.all(
      this.configuration.value.map(async (valueFactoryPath: string) => this.loadModule(valueFactoryPath))
    );

    const valueFactory = valueFactories.find((valueFactory: any) => null !== valueFactory(value));

    if (undefined === valueFactory) {
      throw new Missconfiguration(`Cannot find a value factory for value ${JSON.stringify(value)}.
Did you register well the value factory in your configuration?`);
    }

    return valueFactory(value);
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

export default FilterProvider.create(__moduleConfig as FilterConfiguration, fetcherRegistry, requireContext);
