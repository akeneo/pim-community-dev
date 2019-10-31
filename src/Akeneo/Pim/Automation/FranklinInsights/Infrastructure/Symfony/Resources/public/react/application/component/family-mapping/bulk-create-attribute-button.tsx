/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';

import {ActionButton} from '../app/buttons';
import {Translate} from '../shared/translate';
import {createBulkCreateAttributesModal} from '../../../infrastructure/modal/bulk-create-attributes';

interface Props {
  attributesToCreateCount: number;
  onConfirm: () => void;
}

export const BulkCreateAttributeButton = ({attributesToCreateCount, onConfirm}: Props) => {
  const openModal = () => {
    createBulkCreateAttributesModal(attributesToCreateCount, onConfirm);
  };

  return (
    <ActionButton classNames={['AknButtonList-item']} count={attributesToCreateCount} onClick={openModal}>
      <Translate id={'akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.bulk_create_attribute'} />
    </ActionButton>
  );
};
