import * as _ from 'underscore';
import BaseView = require('pimui/js/view/base');

const __ = require('oro/translator');
const requireContext = require('require-context');
const userContext = require('pim/user-context');

class NoTemplateForAxisError extends Error {}

interface Templates {
  averageMax?: string;
  count?: string;
  [propName: string]: any;
}

interface SectionData {
  [propName: string]: any;
}

interface SectionConfig {
  align: string;
  warningText: string;
  templates: Templates;
  axes: Array<string>;
}

interface Axis {
  value: number | {average: number; max: number};
  hasWarning: boolean;
  type: string;
}

/**
 * Section view for catalog volume screen
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SectionView extends BaseView {
  readonly config: SectionConfig = {
    align: 'left',
    warningText: __('pim_catalog_volume.axis.warning'),
    templates: {
      averageMax: 'pim/template/catalog-volume/average-max',
      count: 'pim/template/catalog-volume/number',
    },
    axes: [],
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: SectionConfig}) {
    super({...options, ...{className: 'AknCatalogVolume-axisContainer'}});

    this.config = {...this.config, ...options.config};
  }

  /**
   * Returns true if the section contains data
   *
   * @param sectionData
   * @param sectionAxes
   */
  sectionHasData(sectionData: SectionData, sectionAxes: string[]): boolean {
    return Object.keys(sectionData).filter(field => sectionAxes.includes(field)).length > 0;
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const sectionData: SectionData = this.getRoot().getFormData();
    const sectionAxes: string[] = this.config.axes;
    const sectionHasData = this.sectionHasData(sectionData, sectionAxes);

    this.$el.empty();

    if (false === sectionHasData) {
      return this;
    }

    if (this.config.align === 'right') {
      this.$el.addClass('AknCatalogVolume-axisContainer--right')
    }

    this.renderAxes(this.config.axes, sectionData);

    return this;
  }

  /**
   * Replaces underscores with dashes given a string
   *
   * @param name
   */
  getIconName(name: string): string {
    return name.replace(/_/g, '-');
  }

  /**
   * Gets the name of the template from the type
   *
   * @param name
   */
  getTemplateName(name: string): string {
    return name
      .toLowerCase()
      .replace(/_(.)/g, letter => letter.toUpperCase())
      .replace(/_/g, '');
  }

  /**
   * Generates the html for each axis depending on the type, appends the axis to the axis container
   * @param  {Array} axes An array of field names for each axis
   * @param  {Object} data An object containing data for each axis
   */
  renderAxes(axes: string[], data: {[key: string]: any}): void {
    axes.forEach((name: string) => {
      const axisData: {[key: string]: any} | undefined = data[name];

      if (undefined === axisData) {
        return;
      }

      const axis: Axis = {
        value: axisData.value,
        hasWarning: axisData.has_warning,
        type: axisData.type,
      };

      const templateName: string = this.getTemplateName(axis.type);
      const typeTemplate: string = this.config.templates[templateName];

      if (undefined === typeTemplate) {
        throw new NoTemplateForAxisError(`The axis ${name} does not have a template for ${axis.type}`);
      }

      const template = _.template(requireContext(typeTemplate));

      const el = template({
        name,
        icon: this.getIconName(name),
        value: axis.value,
        hasWarning: axis.hasWarning,
        title: __(`pim_catalog_volume.axis.${name}`),
        warningText: this.config.warningText,
        meanLabel: __('pim_catalog_volume.mean'),
        maxLabel: __('pim_catalog_volume.max'),
        userLocale: userContext.get('uiLocale').split('_')[0],
      });

      this.$el.append(el);
    });
  }
}

export = SectionView;
