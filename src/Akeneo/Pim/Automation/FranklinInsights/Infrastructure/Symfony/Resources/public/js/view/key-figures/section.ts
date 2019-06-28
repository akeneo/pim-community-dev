/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import Axes from '../../model/key-figure-axes';
import Axis from '../../model/key-figure-axis';

const __ = require('oro/translator');
const requireContext = require('require-context');
const userContext = require('pim/user-context');

class NoTemplateForAxisError extends Error {}

interface Templates {
  number?: string;
  [propName: string]: any;
}

interface SectionConfig {
  align: string;
  templates: Templates;
  axes: string[];
}

/**
 * Section view for catalog volume screen
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SectionView extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
    templates: {
      number: 'akeneo/franklin-insights/template/key-figures/number'
    },
    axes: []
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: SectionConfig}) {
    super({...options, ...{className: 'AknShowFigures-axisContainer'}});

    this.config = {...this.config, ...options.config};
  }

  /**
   * Returns true if the section contains data
   *
   * @param axes
   * @param sectionAxes
   */
  public sectionHasData(axes: Axes, sectionAxes: string[]): boolean {
    return Object.keys(axes).filter(field => sectionAxes.includes(field)).length > 0;
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const sectionData: Axes = this.getRoot().getFormData();
    const sectionAxes: string[] = this.config.axes;
    const sectionHasData = this.sectionHasData(sectionData, sectionAxes);

    this.$el.empty();

    if (false === sectionHasData) {
      return this;
    }

    if (this.config.align === 'right') {
      this.$el.addClass('AknShowFigures-axisContainer--right');
    }

    this.renderAxes(this.config.axes, sectionData);

    return this;
  }

  /**
   * Replaces underscores with dashes given a string
   *
   * @param name
   */
  public getIconName(name: string): string {
    return name.replace(/_/g, '-');
  }

  /**
   * Gets the name of the template from the type
   *
   * @param name
   */
  public getTemplateName(name: string): string {
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
  public renderAxes(axes: string[], data: Axes): void {
    axes.forEach((name: string) => {
      const axisData: {[key: string]: any} | undefined = data[name];

      if (undefined === axisData) {
        return;
      }

      const axis: Axis = {
        value: axisData.value,
        type: axisData.type
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
        title: __(`akeneo_franklin_insights.key_figures.axis.${name}`),
        userLocale: userContext.get('uiLocale').split('_')[0]
      });

      this.$el.append(el);
    });
  }
}

export = SectionView;
