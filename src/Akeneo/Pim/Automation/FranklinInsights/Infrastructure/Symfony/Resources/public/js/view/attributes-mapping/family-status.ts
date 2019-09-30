/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as _ from 'underscore';

import {FamilyMappingStatus} from '../../../react/domain/model/family-mapping-status.enum';
import {AttributesMapping} from '../../model/attributes-mapping';

import BaseView = require('pimui/js/view/base');

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

  public configure(): any {
    super.configure();

    this.getFormModel().on('change', this.render.bind(this));
  }

  public render(): BaseView {
    const {familyMappingStatus} = this.getFormData() as AttributesMapping;

    this.$el.html(
      this.template({
        __,
        familyMappingStatus: this.formatFamilyMappingStatus(familyMappingStatus)
      })
    );

    return BaseView.prototype.render.apply(this, arguments);
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
      case FamilyMappingStatus.PENDING:
        formattedFamilyMappingStatus.className = 'pending';
        formattedFamilyMappingStatus.label = this.config.labels.familyMappingPending;
        break;
      case FamilyMappingStatus.FULL:
        formattedFamilyMappingStatus.className = 'full';
        formattedFamilyMappingStatus.label = this.config.labels.familyMappingFull;
        break;
    }

    return formattedFamilyMappingStatus;
  }
}

export = FamilyStatus;
