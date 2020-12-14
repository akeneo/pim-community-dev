import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {Button, DeleteIllustration, getColor, Helper, Link, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate, useRouter, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {useIsMounted} from '@akeneo-pim-community/shared';

const Content = styled.div`
  margin-bottom: 10px;
`;

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

const useImpactedItemCount = (attributeCode: string) => {
  const [productCount, setProductCount] = useState<number>();
  const [productModelCount, setProductModelCount] = useState<number>();
  const [isLoading, setLoading] = useState<boolean>(false);
  const route = useRoute('pim_enrich_count_items_with_attribute_value', {attribute_code: attributeCode});
  const isMounted = useIsMounted();

  const fetchImpactedItemCount = async () => {
    setLoading(true);
    const response = await fetch(route);
    const json = await response.json();

    if (isMounted()) {
      setLoading(false);
      setProductCount(json.products);
      setProductModelCount(json.product_models);
    }
  };

  useEffect(() => {
    fetchImpactedItemCount();
  }, [route, attributeCode]);

  return [productCount, productModelCount, isLoading] as const;
};

type DeleteModalProps = {
  onCancel: () => void;
  onSuccess: () => void;
  attributeCode: string;
};

const DeleteModal = ({onCancel, onSuccess, attributeCode}: DeleteModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const router = useRouter();
  const [productCount, productModelCount, isLoading] = useImpactedItemCount(attributeCode);

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
          <Highlight className={isLoading ? 'AknLoadingPlaceHolder' : undefined}>
            {translate('pim_enrich.entity.attribute.module.delete.item_count', {
              productCount: productCount?.toString() ?? '',
              productModelCount: productModelCount?.toString() ?? '',
            })}
          </Highlight>
          &nbsp;
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
