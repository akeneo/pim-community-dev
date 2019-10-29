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
import {createBulkAddAttributesToFamilyModal} from '../../../infrastructure/modal/bulk-add-attributes-to-family';

interface Props {
  attributesToAddToFamilyCount: number;
  onConfirm: () => void;
}

export const BulkAddToFamilyButton = ({attributesToAddToFamilyCount, onConfirm}: Props) => {
  const openModal = () => {
    createBulkAddAttributesToFamilyModal(attributesToAddToFamilyCount, onConfirm);
  };

  return (
    <ActionButton classNames={['AknButtonList-item']} count={attributesToAddToFamilyCount} onClick={openModal}>
      <Translate id={'akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.bulk_add_to_family'} />
    </ActionButton>
  );
};
