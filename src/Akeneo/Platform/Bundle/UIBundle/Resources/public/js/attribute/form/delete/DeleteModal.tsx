import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {
  Button,
  DeleteIllustration,
  Field,
  getColor,
  Helper,
  Link,
  Modal,
  SectionTitle,
  TextInput,
  Title,
} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {useIsMounted} from '@akeneo-pim-community/shared';

const SpacedHelper = styled(Helper)`
  margin: 10px 0 20px;
`;

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

const useImpactedItemCount = (attributeCode: string) => {
  const [productCount, setProductCount] = useState<number>(0);
  const [productModelCount, setProductModelCount] = useState<number>(0);
  const route = useRoute('pim_enrich_count_items_with_attribute_value', {attribute_code: attributeCode});
  const isMounted = useIsMounted();

  const fetchImpactedItemCount = async () => {
    const response = await fetch(route);
    const json = await response.json();

    if (isMounted()) {
      setProductCount(json.products);
      setProductModelCount(json.product_models);
    }
  };

  useEffect(() => {
    fetchImpactedItemCount();
  }, [route, attributeCode]);

  return [productCount, productModelCount] as const;
};

type DeleteModalProps = {
  onCancel: () => void;
  onSuccess: () => void;
  attributeCode: string;
};

const DeleteModal = ({onCancel, onSuccess, attributeCode}: DeleteModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const removeRoute = useRoute('pim_enrich_attribute_rest_remove', {code: attributeCode});
  const [productCount, productModelCount] = useImpactedItemCount(attributeCode);
  const [attributeCodeConfirm, setAttributeCodeConfirm] = useState<string>('');

  const handleConfirm = () => {
    fetch(removeRoute, {
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

  const productText =
    0 < productCount
      ? translate(
          'pim_enrich.entity.attribute.module.delete.product_count',
          {count: productCount.toString()},
          productCount
        )
      : '';
  const productModelText =
    0 < productModelCount
      ? translate(
          'pim_enrich.entity.attribute.module.delete.product_model_count',
          {count: productModelCount.toString()},
          productModelCount
        )
      : '';
  const impactedItemsText = `${productText}${
    0 < productCount && 0 < productModelCount ? ` ${translate('pim_common.and')} ` : ''
  }${productModelText}`;

  return (
    <Modal
      isOpen={true}
      onClose={onCancel}
      closeTitle={translate('pim_common.close')}
      illustration={<DeleteIllustration />}
    >
      <SectionTitle color="brand">{translate('pim_enrich.entity.attribute.plural_label')}</SectionTitle>
      <Title>{translate('pim_common.confirm_deletion')}</Title>
      {translate('pim_enrich.entity.attribute.module.delete.confirm')}
      {(0 < productCount || 0 < productModelCount) && (
        <p>
          <Highlight>{impactedItemsText}</Highlight>
          &nbsp;
          {translate('pim_enrich.entity.attribute.module.delete.used')}
        </p>
      )}
      <SpacedHelper>
        {translate('pim_enrich.entity.attribute.module.delete.helper.content')}
        <Link href="https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#delete-an-attribute-and-keep-the-related-data">
          {translate('pim_enrich.entity.attribute.module.delete.helper.link')}
        </Link>
      </SpacedHelper>
      <Field label={translate('pim_enrich.entity.attribute.module.delete.type', {attributeCode})}>
        <TextInput name="attribute_confirm" value={attributeCodeConfirm} onChange={setAttributeCodeConfirm} />
      </Field>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel}>
          {translate('pim_common.cancel')}
        </Button>
        <Button disabled={attributeCodeConfirm !== attributeCode} level="danger" onClick={handleConfirm}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteModal};
