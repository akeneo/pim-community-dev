import * as Backbone from 'backbone';
import {translate} from '../translator';

export const createBulkCreateAttributesModal = (attributesToCreateCount: number, onConfirm: () => void) => {
  const modal = new (Backbone as any).BootstrapModal({
    picture: 'illustrations/Attribute.svg',
    title: translate(
      'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.title',
      {count: attributesToCreateCount},
      attributesToCreateCount
    ),
    subtitle: translate('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.subtitle'),
    innerDescription: translate(
      'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.description',
      {},
      attributesToCreateCount
    ),
    content: `
          <div class="AknMessageBox AknMessageBox--warning AknMessageBox--withIcon">
            ${translate(
              'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.warning',
              {count: attributesToCreateCount},
              attributesToCreateCount
            )}
          </div>
        `,
    okText: translate('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.ok'),
    cancelText: ''
  });

  modal.open();

  modal.listenTo(modal, 'ok', onConfirm);
};
