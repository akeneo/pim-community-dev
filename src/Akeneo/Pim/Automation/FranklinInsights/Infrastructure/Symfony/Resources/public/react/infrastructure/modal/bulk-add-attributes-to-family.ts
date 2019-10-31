import * as Backbone from 'backbone';
import {translate} from '../translator';

export const createBulkAddAttributesToFamilyModal = (attributesToAddToFamilyCount: number, onConfirm: () => void) => {
  const modal = new (Backbone as any).BootstrapModal({
    picture: 'illustrations/Family.svg',
    title: translate(
      'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_add_to_family.title',
      {count: attributesToAddToFamilyCount},
      attributesToAddToFamilyCount
    ),
    subtitle: translate('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_add_to_family.subtitle'),
    innerDescription: translate(
      'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_add_to_family.description',
      {},
      attributesToAddToFamilyCount
    ),
    content: `
          <div class="AknMessageBox AknMessageBox--warning AknMessageBox--withIcon">
            ${translate(
              'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_add_to_family.warning',
              {count: attributesToAddToFamilyCount},
              attributesToAddToFamilyCount
            )}
          </div>
        `,
    okText: translate('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_add_to_family.ok'),
    cancelText: ''
  });

  modal.open();

  modal.listenTo(modal, 'ok', onConfirm);
};
