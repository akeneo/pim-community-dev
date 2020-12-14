import React from 'react';
import styled from 'styled-components';
import {Button, DeleteIllustration, getColor, Helper, Link, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate, useRouter} from '@akeneo-pim-community/legacy-bridge';

const Content = styled.div`
  margin-bottom: 10px;
`;

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

type DeleteModalProps = {
  onCancel: () => void;
  onSuccess: () => void;
  attributeCode: string;
};

const DeleteModal = ({onCancel, onSuccess, attributeCode}: DeleteModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const router = useRouter();

  const handleConfirm = () => {
    fetch(router.generate('pim_enrich_attribute_rest_remove', {code: attributeCode}), {
      method: 'DELETE',
      headers: new Headers({
        'X-Requested-With': 'XMLHttpRequest',
      }),
    })
      .then(async (response: Response) => {
        if (response.ok) {
          notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.attribute.flash.delete.success'));
          onSuccess();
        } else {
          notify(
            NotificationLevel.ERROR,
            (await response.json()).message ?? translate('pim_enrich.entity.attribute.flash.delete.fail')
          );
        }
      })
      .catch(() => {
        notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute.flash.delete.fail'));
      });
  };

  return (
    <Modal
      isOpen={true}
      onClose={onCancel}
      closeTitle={translate('pim_common.close')}
      illustration={<DeleteIllustration />}
    >
      <SectionTitle color="brand">{translate('pim_enrich.entity.attribute.plural_label')}</SectionTitle>
      <Title>{translate('pim_common.confirm_deletion')}</Title>
      <Content>
        {translate('pim_enrich.entity.attribute.module.delete.confirm')}
        <p>
          <Highlight>
            {translate('pim_enrich.entity.attribute.module.delete.item_count', {
              productCount: 'TODO',
              productModelCount: 'TODO',
            })}
          </Highlight>{' '}
          {translate('pim_enrich.entity.attribute.module.delete.used')}
        </p>
      </Content>
      <Helper>
        {translate('pim_enrich.entity.attribute.module.delete.helper.content')}
        <Link href="https://help.akeneo.com/pim/v4/articles/manage-your-attributes.html#Delete-an-attribute-and-keeping-the-related-data">
          {translate('pim_enrich.entity.attribute.module.delete.helper.link')}
        </Link>
      </Helper>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={handleConfirm}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteModal};
