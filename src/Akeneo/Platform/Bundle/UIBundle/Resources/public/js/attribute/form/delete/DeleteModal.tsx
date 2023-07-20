import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {Field, getColor, Helper, Link, TextInput} from 'akeneo-design-system';
import {
  useIsMounted,
  DeleteModal as BaseDeleteModal,
  NotificationLevel,
  useNotify,
  useTranslate,
  useRoute,
  useTranslateWithComponents,
} from '@akeneo-pim-community/shared';
const FetcherRegistry = require('pim/fetcher-registry');

const SpacedHelper = styled(Helper)`
  margin: 10px 0 20px;
`;

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

const useMainIdentifierCode = () => {
  const [mainIdentifierCode, setMainIdentifierCode] = useState<string | undefined>(undefined);
  useEffect(() => {
    FetcherRegistry.getFetcher('attribute')
      .getIdentifierAttribute()
      .then((mainIdentifierAttribute: {code: string}) => setMainIdentifierCode(mainIdentifierAttribute.code));
  });

  return mainIdentifierCode;
};

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
  const translateWithComponents = useTranslateWithComponents();
  const notify = useNotify();
  const removeRoute = useRoute('pim_enrich_attribute_rest_remove', {code: attributeCode});
  const [productCount, productModelCount] = useImpactedItemCount(attributeCode);
  const mainIdentifierCode = useMainIdentifierCode();
  const [attributeCodeConfirm, setAttributeCodeConfirm] = useState<string>('');
  const [isLoading, setLoading] = useState<boolean>(false);
  const isValid = attributeCodeConfirm === attributeCode;
  const isMainIdentifier = mainIdentifierCode === attributeCode;

  const handleConfirm = async () => {
    if (!isValid || isLoading) return;

    try {
      setLoading(true);
      const response = await fetch(removeRoute, {
        method: 'DELETE',
        headers: new Headers({
          'X-Requested-With': 'XMLHttpRequest',
        }),
      });
      setLoading(false);

      if (response.ok) {
        notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.attribute.flash.delete.success'));
        onSuccess();
      } else {
        const {message} = await response.json();
        //try to translate a key if there is one
        notify(
          NotificationLevel.ERROR,
          message ? translate(message) : translate('pim_enrich.entity.attribute.flash.delete.fail')
        );
      }
    } catch (error) {
      setLoading(false);
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute.flash.delete.fail'));
    }
  };

  const productText =
    0 < productCount
      ? translate('pim_enrich.entity.attribute.module.delete.product_count', {count: productCount}, productCount)
      : '';
  const productModelText =
    0 < productModelCount
      ? translate(
          'pim_enrich.entity.attribute.module.delete.product_model_count',
          {count: productModelCount},
          productModelCount
        )
      : '';
  const impactedItemsText = `${productText}${
    0 < productCount && 0 < productModelCount ? ` ${translate('pim_common.and')} ` : ''
  }${productModelText}`;

  return (
    <BaseDeleteModal
      title={translate('pim_enrich.entity.attribute.plural_label')}
      onConfirm={handleConfirm}
      onCancel={onCancel}
      canConfirmDelete={isValid}
    >
      {isMainIdentifier ? (
        <Helper level="error">
          {translateWithComponents('pim_enrich.entity.attribute.module.delete.cannot_delete', {
            link: innerText => (
              <Link
                href="https://help.akeneo.com/en_US/serenity-build-your-catalog/33-serenity-manage-your-product-identifiers"
                target="_blank"
              >
                {innerText}
              </Link>
            ),
          })}
        </Helper>
      ) : (
        <>
          {translate('pim_enrich.entity.attribute.module.delete.confirm')}
          {(0 < productCount || 0 < productModelCount) && (
            <p>
              {translate('pim_enrich.entity.attribute.module.delete.attribute_removal')}{' '}
              <Highlight>{impactedItemsText}</Highlight>{' '}
              {translate('pim_enrich.entity.attribute.module.delete.used', {}, productCount + productModelCount)}
            </p>
          )}
          <SpacedHelper>
            {translate('pim_enrich.entity.attribute.module.delete.helper.content')}{' '}
            <Link href="https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#delete-an-attribute-and-keep-the-related-data">
              {translate('pim_enrich.entity.attribute.module.delete.helper.link')}
            </Link>
          </SpacedHelper>
          <Field label={translate('pim_enrich.entity.attribute.module.delete.type', {attributeCode})}>
            <TextInput
              readOnly={isLoading}
              value={attributeCodeConfirm}
              onChange={setAttributeCodeConfirm}
              onSubmit={handleConfirm}
            />
          </Field>
        </>
      )}
    </BaseDeleteModal>
  );
};

export {DeleteModal};
