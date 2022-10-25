import React from 'react';
import styled from 'styled-components';
import {filterErrors, formatParameters, useTranslate, useSecurity, useRouter} from '@akeneo-pim-community/shared';
import {
  BrokenLinkIcon,
  EditIcon,
  CloseIcon,
  IconButton,
  NumberInput,
  Image,
  Table,
  useTheme,
  Helper,
  Badge,
} from 'akeneo-design-system';
import {ProductType, Row, QuantifiedLink, MAX_QUANTITY} from '../models';
import {useProductThumbnail} from '../hooks';

const CellPlaceholder = styled.div`
  height: 40px;
  width: 100%;
`;

type QuantifiedAssociationRowProps = {
  row: Row;
  parentQuantifiedLink: QuantifiedLink | undefined;
  isUserOwner?: boolean;
  isCompact?: boolean;
  onChange: (row: Row) => void;
  onRemove: (row: Row) => void;
};

const QuantifiedAssociationRow = ({
  row,
  parentQuantifiedLink,
  isCompact = false,
  isUserOwner = true,
  onChange,
  onRemove,
}: QuantifiedAssociationRowProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const isProductModel = ProductType.ProductModel === row.productType;
  const router = useRouter();
  const productEditUrl = isProductModel
    ? router.generate('pim_enrich_product_model_edit', {id: row.product?.id.toString() || ''})
    : router.generate('pim_enrich_product_edit', {uuid: row.product?.id.toString() || ''});
  const thumbnailUrl = useProductThumbnail(row.product);
  const blueColor = useTheme().color.blue100;
  const canRemoveAssociation =
    isGranted('pim_enrich_associations_remove') && undefined === parentQuantifiedLink && isUserOwner;
  const canUpdateQuantity =
    isGranted('pim_enrich_associations_edit') && isGranted('pim_enrich_associations_remove') && isUserOwner;

  const handleQuantityChange = (value: string) => {
    if (!canUpdateQuantity) return;

    const numberValue = Number(value) || 1;
    const limitedValue = numberValue > MAX_QUANTITY ? row.quantifiedLink.quantity : numberValue;

    onChange({
      ...row,
      quantifiedLink: {...row.quantifiedLink, quantity: limitedValue},
    });
  };

  return (
    <Table.Row>
      <Table.Cell>
        {null === row.product ? (
          <CellPlaceholder className="AknLoadingPlaceHolder" />
        ) : (
          <Image
            fit="contain"
            isStacked={isProductModel}
            width={44}
            height={44}
            src={thumbnailUrl}
            alt={row.product.label}
          />
        )}
      </Table.Cell>
      <Table.Cell rowTitle={!isProductModel}>
        {null === row.product ? <CellPlaceholder className="AknLoadingPlaceHolder" /> : row.product.label}
      </Table.Cell>
      <Table.Cell>
        {null === row.product ? (
          <CellPlaceholder className="AknLoadingPlaceHolder" />
        ) : (
          row.product?.identifier ?? `[${row.product.id}]`
        )}
      </Table.Cell>
      {!isCompact && (
        <>
          <Table.Cell>
            {null === row.product ? (
              <CellPlaceholder className="AknLoadingPlaceHolder" />
            ) : null === row.product.completeness ? (
              translate('pim_common.not_available')
            ) : (
              <Badge>{row.product.completeness}%</Badge>
            )}
          </Table.Cell>
          <Table.Cell>
            {null === row.product ? (
              <CellPlaceholder className="AknLoadingPlaceHolder" />
            ) : null === row.product.variant_product_completenesses ? (
              translate('pim_common.not_available')
            ) : (
              <Badge>
                {row.product.variant_product_completenesses.completeChildren} /{' '}
                {row.product.variant_product_completenesses.totalChildren}
              </Badge>
            )}
          </Table.Cell>
        </>
      )}
      <Table.Cell width={150}>
        <NumberInput
          title={translate('pim_enrich.entity.product.module.associations.quantified.quantity')}
          type="number"
          min={1}
          max={MAX_QUANTITY}
          value={row.quantifiedLink.quantity.toString()}
          invalid={0 < filterErrors(row.errors, 'quantity').length}
          disabled={!canUpdateQuantity}
          onChange={handleQuantityChange}
        />
        {formatParameters(row.errors).map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Table.Cell>
      <Table.Cell>
        {!isCompact && (
          <>
            {undefined !== parentQuantifiedLink && parentQuantifiedLink.quantity !== row.quantifiedLink.quantity && (
              <BrokenLinkIcon
                color={blueColor}
                title={translate('pim_enrich.entity.product.module.associations.quantified.unlinked')}
              />
            )}
            {null !== row.product && (
              <IconButton
                level="tertiary"
                ghost="borderless"
                icon={<EditIcon />}
                title={translate('pim_enrich.entity.product.module.associations.edit')}
                href={`#${productEditUrl}`}
                target="_blank"
              />
            )}
          </>
        )}
        {canRemoveAssociation && (
          <IconButton
            level="tertiary"
            ghost="borderless"
            icon={<CloseIcon />}
            title={translate('pim_enrich.entity.product.module.associations.remove')}
            onClick={() => onRemove(row)}
          />
        )}
      </Table.Cell>
    </Table.Row>
  );
};

export {QuantifiedAssociationRow};
