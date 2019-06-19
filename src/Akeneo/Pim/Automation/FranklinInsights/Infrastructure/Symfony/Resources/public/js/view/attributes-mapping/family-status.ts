/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import AttributeMappingStatus from '../../model/attribute-mapping-status';
import AttributesMapping from '../../model/attributes-mapping';
import AttributesMappingForFamily from '../../model/attributes-mapping-for-family';
import FamilyMappingStatus from '../../model/family-mapping-status';

const __ = require('oro/translator');
const template = require('akeneo/franklin-insights/template/settings/attributes-mapping/family-status');

interface Config {
  labels: {
    familyMappingPending: string;
    familyMappingFull: string;
  };
}

/**
 * Container for the FamilySelector and display the mapping status of the selected family.
 *
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
class FamilyStatus extends BaseView {
  private readonly template = _.template(template);

  private readonly config: Config = {
    labels: {
      familyMappingPending: '',
      familyMappingFull: ''
    }
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  public render(): BaseView {
    const familyMapping: AttributesMappingForFamily = this.getFormData();
    const mapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};

    const familyMappingStatus = this.getFamilyMappingStatus(mapping);

    this.$el.html(
      this.template({
        __,
        familyMappingStatus: this.formatFamilyMappingStatus(familyMappingStatus)
      })
    );

    return BaseView.prototype.render.apply(this, arguments);
  }

  /**
   * @param {AttributesMapping} mapping
   *
   * @return {number}
   */
  private getFamilyMappingStatus(mapping: AttributesMapping): number {
    const franklinAttributes = Object.keys(mapping);
    let status = FamilyMappingStatus.MAPPING_FULL;

    if (0 === franklinAttributes.length) {
      status = FamilyMappingStatus.MAPPING_EMPTY;
    }

    franklinAttributes.forEach((franklinAttribute: string) => {
      if (AttributeMappingStatus.ATTRIBUTE_PENDING === mapping[franklinAttribute].status) {
        status = FamilyMappingStatus.MAPPING_PENDING;
      }
    });

    return status;
  }

  /**
   * Format the message (label and style) that will be display on the view
   * according to the status of the family mapping.
   *
   * @param {number} familyMappingStatus
   *
   * @return {object}
   */
  private formatFamilyMappingStatus(familyMappingStatus: number): {className: string; label: string} {
    const formattedFamilyMappingStatus = {
      className: '',
      label: ''
    };

    switch (familyMappingStatus) {
      case FamilyMappingStatus.MAPPING_PENDING:
        formattedFamilyMappingStatus.className = 'pending';
        formattedFamilyMappingStatus.label = this.config.labels.familyMappingPending;
        break;
      case FamilyMappingStatus.MAPPING_FULL:
        formattedFamilyMappingStatus.className = 'full';
        formattedFamilyMappingStatus.label = this.config.labels.familyMappingFull;
        break;
    }

    return formattedFamilyMappingStatus;
  }
}

export = FamilyStatus;
