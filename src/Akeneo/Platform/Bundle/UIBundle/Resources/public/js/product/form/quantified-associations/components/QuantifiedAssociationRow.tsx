import React from 'react';
import styled from 'styled-components';
import {useTranslate, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {TransparentButton, EditIcon, CloseIcon} from '@akeneo-pim-community/shared';
import {ProductType, RowWithProduct, Row} from '../models';

const Container = styled.tr`
  height: 74px;
  border-bottom: 1px solid ${props => props.theme.color.grey70};

  :hover {
    background-color: ${props => props.theme.color.grey60};
  }

  td:first-child {
    padding-left: 15px;
  }

  td {
    width: 15%;
  }
`;

const Thumbnail = styled.img`
  width: 44px;
  height: 44px;
  border: 1px solid ${({theme}) => theme.color.grey80};
`;

const LabelCell = styled.td<{isProduct: boolean}>`
  font-style: italic;
  font-weight: bold;
  color: ${({theme, isProduct}) => (isProduct ? theme.color.purple100 : 'inherit')};
  min-width: 200px;
`;

const Badge = styled.span`
  font-size: ${props => props.theme.fontSize.small};
  border-radius: 2px;
  background-color: ${props => props.theme.color.white};
  border: 1px solid ${props => props.theme.color.green100};
  color: ${props => props.theme.color.green140};
  padding: 2px 5px;
`;

const QuantityInput = styled.input`
  border: 1px solid ${props => props.theme.color.grey80};
  width: 100px;
  height: 40px;
  padding: 12px 15px;
  color: inherit;
`;

const RowActions = styled.div`
  display: flex;
  padding: 0 20px;
`;

const RowAction = styled(TransparentButton)`
  :not(:first-child) {
    margin-left: 20px;
  }
`;

type QuantifiedAssociationRowProps = {
  row: RowWithProduct;
  onChange: (row: Row) => void;
  onRowDelete: (row: Row) => void;
};

const QuantifiedAssociationRow = ({row, onChange, onRowDelete}: QuantifiedAssociationRowProps) => {
  const translate = useTranslate();
  const isProduct = ProductType.Product === row.productType;
  const productEditUrl = useRoute(`pim_enrich_${row.productType}_edit`, {id: row.product.id.toString()});

  return (
    <Container>
      <td>
        <Thumbnail
          src={null !== row.product.image ? row.product.image.filePath : '/bundles/pimui/img/image_default.png'}
        />
      </td>
      <LabelCell isProduct={isProduct}>{row.product.label}</LabelCell>
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
          onChange={event => onChange({...row, quantity: Number(event.currentTarget.value)})}
        />
      </td>
      <td>
        <RowActions>
          <RowAction>
            <a href={`#${productEditUrl}`} target="_blank">
              <EditIcon size={20} />
            </a>
          </RowAction>
          <RowAction onClick={() => onRowDelete(row)}>
            <CloseIcon title={translate('pim_enrich.entity.product.module.associations.remove')} size={20} />
          </RowAction>
        </RowActions>
      </td>
    </Container>
  );
};

export {QuantifiedAssociationRow};
