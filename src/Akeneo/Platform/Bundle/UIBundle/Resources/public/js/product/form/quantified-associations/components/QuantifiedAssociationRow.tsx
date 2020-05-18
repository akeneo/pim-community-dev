import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {TransparentButton, EditIcon, CloseIcon} from '@akeneo-pim-community/shared';
import {QuantifiedLink, Product} from '../models';

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
  product: Product;
  quantifiedLink: QuantifiedLink;
  onChange: (updatedQuantifiedLink: QuantifiedLink) => void;
};

const QuantifiedAssociationRow = ({product, quantifiedLink, onChange}: QuantifiedAssociationRowProps) => {
  const translate = useTranslate();
  const isProduct = 'product' === product.document_type;

  return (
    <Container>
      <td>
        <Thumbnail src={null !== product.image ? product.image.filePath : '/bundles/pimui/img/image_default.png'} />
      </td>
      <LabelCell isProduct={isProduct}>{product.label}</LabelCell>
      <td>{product.identifier}</td>
      <td>
        {null === product.completeness ? translate('pim_common.not_available') : <Badge>{product.completeness}%</Badge>}
      </td>
      <td>
        {null === product.variant_product_completenesses ? (
          translate('pim_common.not_available')
        ) : (
          <Badge>
            {product.variant_product_completenesses.completeChildren} /{' '}
            {product.variant_product_completenesses.totalChildren}
          </Badge>
        )}
      </td>
      <td>
        <QuantityInput
          title={translate('pim_enrich.entity.product.module.associations.quantified.quantity')}
          type="number"
          min={1}
          value={quantifiedLink.quantity}
          onChange={event => onChange({...quantifiedLink, quantity: event.currentTarget.value})}
        />
      </td>
      <td>
        <RowActions>
          <RowAction>
            <EditIcon size={20} />
          </RowAction>
          <RowAction>
            <CloseIcon title={translate('pim_enrich.entity.product.module.associations.remove')} size={20} />
          </RowAction>
        </RowActions>
      </td>
    </Container>
  );
};

export {QuantifiedAssociationRow};
