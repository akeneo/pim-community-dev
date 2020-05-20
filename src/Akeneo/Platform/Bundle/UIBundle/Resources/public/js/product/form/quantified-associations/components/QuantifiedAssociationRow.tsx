import React from 'react';
import styled, {css} from 'styled-components';
import {useTranslate, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {TransparentButton, EditIcon, CloseIcon} from '@akeneo-pim-community/shared';
import {ProductType, RowWithProduct, Row} from '../models';
import {useProductThumbnail} from '../hooks';

const Container = styled.tr`
  height: 74px;
  border-bottom: 1px solid ${({theme}) => theme.color.grey70};

  :hover {
    background-color: ${({theme}) => theme.color.grey60};
  }

  td:first-child {
    padding-left: 15px;
  }
`;

const Thumbnail = styled.div<{isProductModel: boolean}>`
  width: 44px;
  height: 44px;

  img {
    border: 1px solid ${({theme}) => theme.color.grey80};
    width: inherit;
    ${({isProductModel, theme}) =>
      isProductModel &&
      css`
        box-shadow: 1px -2px 0 -1px ${theme.color.white}, 2px -2px 0 -1px ${theme.color.white},
          1px -2px 0 ${theme.color.grey80}, 2px -2px 0 ${theme.color.grey80}, 4px -4px 0 -1px ${theme.color.white},
          2px -4px 0 -1px ${theme.color.white}, 2px -4px 0 ${theme.color.grey80}, 4px -4px 0 ${theme.color.grey80};
      `}
  }
`;

const LabelCell = styled.td<{isProductModel: boolean}>`
  font-style: italic;
  font-weight: bold;
  color: ${({theme, isProductModel}) => (isProductModel ? 'inherit' : theme.color.purple100)};
  min-width: 200px;
`;

const Badge = styled.span`
  font-size: ${({theme}) => theme.fontSize.small};
  border-radius: 2px;
  background-color: ${({theme}) => theme.color.white};
  border: 1px solid ${({theme}) => theme.color.green100};
  color: ${({theme}) => theme.color.green140};
  padding: 2px 5px;
`;

const QuantityInput = styled.input`
  border: 1px solid ${({theme}) => theme.color.grey80};
  width: 100px;
  height: 40px;
  padding: 12px 15px;
  color: inherit;
`;

const RowActions = styled.div`
  display: flex;
  padding: 0 20px;
  justify-content: flex-end;
`;

const RowAction = styled(TransparentButton)`
  :not(:first-child) {
    margin-left: 20px;
  }

  a {
    display: flex;
  }
`;

type QuantifiedAssociationRowProps = {
  row: RowWithProduct;
  onChange: (row: Row) => void;
  onRemove: (row: Row) => void;
};

const QuantifiedAssociationRow = ({row, onChange, onRemove}: QuantifiedAssociationRowProps) => {
  const translate = useTranslate();
  const isProductModel = ProductType.ProductModel === row.productType;
  const productEditUrl = useRoute(`pim_enrich_${row.productType}_edit`, {id: row.product.id.toString()});
  const thumbnailUrl = useProductThumbnail(row.product);

  return (
    <Container>
      <td>
        <Thumbnail isProductModel={isProductModel}>
          <img src={thumbnailUrl} alt={row.product.label} />
        </Thumbnail>
      </td>
      <LabelCell isProductModel={isProductModel}>{row.product.label}</LabelCell>
      <td>{row.product.identifier}</td>
      <td>
        {null === row.product.completeness ? (
          translate('pim_common.not_available')
        ) : (
          <Badge>{row.product.completeness}%</Badge>
        )}
      </td>
      <td>
        {null === row.product.variant_product_completenesses ? (
          translate('pim_common.not_available')
        ) : (
          <Badge>
            {row.product.variant_product_completenesses.completeChildren} /{' '}
            {row.product.variant_product_completenesses.totalChildren}
          </Badge>
        )}
      </td>
      <td>
        <QuantityInput
          title={translate('pim_enrich.entity.product.module.associations.quantified.quantity')}
          type="number"
          min={1}
          value={row.quantity}
          onChange={event => onChange({...row, quantity: Number(event.currentTarget.value) || 1})}
        />
      </td>
      <td>
        <RowActions>
          <RowAction>
            <a href={`#${productEditUrl}`} target="_blank">
              <EditIcon size={20} />
            </a>
          </RowAction>
          <RowAction onClick={() => onRemove(row)}>
            <CloseIcon title={translate('pim_enrich.entity.product.module.associations.remove')} size={20} />
          </RowAction>
        </RowActions>
      </td>
    </Container>
  );
};

export {QuantifiedAssociationRow};
