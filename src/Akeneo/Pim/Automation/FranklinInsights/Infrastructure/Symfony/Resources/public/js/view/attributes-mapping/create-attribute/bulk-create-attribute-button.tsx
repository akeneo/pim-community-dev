/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as Backbone from 'backbone';
import * as React from 'react';
import {Component} from 'react';

import ActionButton from '../../../common/action-button';

const __ = require('oro/translator');

interface Props {
  count: number;
  onClick: () => void;
}

export class BulkCreateAttributeButton extends Component<Props> {
  public render() {
    return (
      <ActionButton
        className='AknButtonList-item'
        label={__('akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.bulk_create_attribute')}
        count={this.props.count}
        onClick={this.onClick.bind(this)}
      />
    );
  }

  private onClick() {
    const modal = new (Backbone as any).BootstrapModal({
      picture: 'illustrations/Attribute.svg',
      title: __(
        'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.title',
        {count: this.props.count},
        this.props.count
      ),
      subtitle: __('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.subtitle'),
      innerDescription: __(
        'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.description',
        undefined,
        this.props.count
      ),
      content: `
        <div class="AknMessageBox AknMessageBox--warning AknMessageBox--withIcon">
          ${__(
            'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.warning',
            {count: this.props.count},
            this.props.count
          )}
        </div>
      `,
      okText: __('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.ok'),
      cancelText: ''
    });

    modal.open();

    modal.listenTo(modal, 'ok', () => this.props.onClick());
  }
}

export default BulkCreateAttributeButton;
