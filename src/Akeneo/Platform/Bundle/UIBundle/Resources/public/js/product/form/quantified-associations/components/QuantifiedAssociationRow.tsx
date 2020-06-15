import React from 'react';
import styled, {css} from 'styled-components';
import {useTranslate, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {
  TransparentButton,
  EditIcon,
  CloseIcon,
  UnlinkIcon,
  useAkeneoTheme,
  filterErrors,
  InputErrors,
} from '@akeneo-pim-community/shared';
import {ProductType, Row, QuantifiedLink} from '../models';
import {useProductThumbnail} from '../hooks';

const Container = styled.tr`
  height: 74px;
  border-bottom: 1px solid ${({theme}) => theme.color.grey70};

  td:first-child {
    padding-left: 15px;
  }
`;

const CellContainer = styled.div`
  display: flex;
  height: 74px;
  align-items: center;
`;
const InputCellContainer = styled(CellContainer)`
  margin-bottom: -19px;
`;

const ActionCellContainer = styled(CellContainer)`
  justify-content: flex-end;
`;

const Cell = styled.td`
  vertical-align: top;
`;

const CellPlaceholder = styled.div`
  height: 54px;
  margin: 10px;
`;

const Thumbnail = styled.div<{isProductModel: boolean}>`
  width: 44px;
  height: 44px;

  img {
    border: 1px solid ${({theme}) => theme.color.grey80};
    width: inherit;
    height: inherit;
    object-fit: contain;
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
  vertical-align: top;
`;

const Badge = styled.span`
  font-size: ${({theme}) => theme.fontSize.small};
  border-radius: 2px;
  background-color: ${({theme}) => theme.color.white};
  border: 1px solid ${({theme}) => theme.color.green100};
  color: ${({theme}) => theme.color.green140};
  padding: 2px 5px;
`;

const QuantityInput = styled.input<{isInvalid: boolean}>`
  border: 1px solid;
  width: 100px;
  height: 40px;
  padding: 12px 15px;
  color: inherit;
  border-color: ${({theme, isInvalid}) => (isInvalid ? theme.color.red100 : theme.color.grey80)};
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
  row: Row;
  parentQuantifiedLink: QuantifiedLink | undefined;
  onChange: (row: Row) => void;
  onRemove: (row: Row) => void;
};

const QuantifiedAssociationRow = ({row, parentQuantifiedLink, onChange, onRemove}: QuantifiedAssociationRowProps) => {
  const translate = useTranslate();
  const isProductModel = ProductType.ProductModel === row.productType;
  const productEditUrl = useRoute(`pim_enrich_${row.productType}_edit`, {id: row.product?.id.toString() || ''});
  const thumbnailUrl = useProductThumbnail(row.product);
  const blueColor = useAkeneoTheme().color.blue100;

  return (
    <>
      <Container>
        <Cell>
          {null === row.product ? (
            <CellPlaceholder className="AknLoadingPlaceHolder" />
          ) : (
            <CellContainer>
              <Thumbnail isProductModel={isProductModel}>
                <img src={thumbnailUrl} alt={row.product.label} />
              </Thumbnail>
            </CellContainer>
          )}
        </Cell>
        <LabelCell isProductModel={isProductModel}>
          {null === row.product ? (
            <CellPlaceholder className="AknLoadingPlaceHolder" />
          ) : (
            <CellContainer>{row.product.label}</CellContainer>
          )}
        </LabelCell>
        <Cell>
          <CellContainer>{row.quantifiedLink.identifier}</CellContainer>
        </Cell>
        <Cell>
          {null === row.product ? (
            <CellPlaceholder className="AknLoadingPlaceHolder" />
          ) : (
            <CellContainer>
              {null === row.product.completeness ? (
                translate('pim_common.not_available')
              ) : (
                <Badge>{row.product.completeness}%</Badge>
              )}
            </CellContainer>
          )}
        </Cell>
        <Cell>
          {null === row.product ? (
            <CellPlaceholder className="AknLoadingPlaceHolder" />
          ) : (
            <CellContainer>
              {null === row.product.variant_product_completenesses ? (
                translate('pim_common.not_available')
              ) : (
                <Badge>
                  {row.product.variant_product_completenesses.completeChildren} /{' '}
                  {row.product.variant_product_completenesses.totalChildren}
                </Badge>
              )}
            </CellContainer>
          )}
        </Cell>
        <Cell>
          <InputCellContainer>
            <QuantityInput
              title={translate('pim_enrich.entity.product.module.associations.quantified.quantity')}
              type="number"
              min={1}
              value={row.quantifiedLink.quantity}
              isInvalid={0 < filterErrors(row.errors, 'quantity').length}
              onChange={event =>
                onChange({
                  ...row,
                  quantifiedLink: {...row.quantifiedLink, quantity: Number(event.currentTarget.value) || 1},
                })
              }
            />
          </InputCellContainer>
          <InputErrors errors={row.errors} />
        </Cell>
        <Cell>
          <ActionCellContainer>
            <RowActions>
              {undefined !== parentQuantifiedLink && parentQuantifiedLink.quantity !== row.quantifiedLink.quantity && (
                <UnlinkIcon
                  color={blueColor}
                  title={translate('pim_enrich.entity.product.module.associations.quantified.unlinked')}
                />
              )}
              {null !== row.product && (
                <RowAction>
                  <a href={`#${productEditUrl}`} target="_blank">
                    <EditIcon title={translate('pim_enrich.entity.product.module.associations.edit')} size={20} />
                  </a>
                </RowAction>
              )}

              {undefined === parentQuantifiedLink && (
                <RowAction onClick={() => onRemove(row)}>
                  <CloseIcon title={translate('pim_enrich.entity.product.module.associations.remove')} size={20} />
                </RowAction>
              )}
            </RowActions>
          </ActionCellContainer>
        </Cell>
      </Container>
    </>
  );
};

export {QuantifiedAssociationRow};
