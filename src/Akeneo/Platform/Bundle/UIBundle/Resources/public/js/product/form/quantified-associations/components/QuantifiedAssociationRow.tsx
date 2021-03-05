import React from 'react';
import styled, {css} from 'styled-components';
import {useTranslate, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {filterErrors, formatParameters} from '@akeneo-pim-community/shared';
import {BrokenLinkIcon, EditIcon, CloseIcon, useTheme, Helper, Badge} from 'akeneo-design-system';
import {ProductType, Row, QuantifiedLink, MAX_QUANTITY} from '../models';
import {useProductThumbnail} from '../hooks';

const Container = styled.tr`
  height: 74px;
  border-bottom: 1px solid ${({theme}) => theme.color.grey80};

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

const RowAction = styled.div`
  height: 20px;
  color: ${({theme}) => theme.color.grey100};
  cursor: pointer;

  :not(:first-child) {
    margin-left: 20px;
  }

  a {
    color: inherit;
  }
`;

type QuantifiedAssociationRowProps = {
  row: Row;
  parentQuantifiedLink: QuantifiedLink | undefined;
  isCompact?: boolean;
  onChange: (row: Row) => void;
  onRemove: (row: Row) => void;
};

const QuantifiedAssociationRow = ({
  row,
  parentQuantifiedLink,
  isCompact = false,
  onChange,
  onRemove,
}: QuantifiedAssociationRowProps) => {
  const translate = useTranslate();
  const isProductModel = ProductType.ProductModel === row.productType;
  const productEditUrl = useRoute(`pim_enrich_${row.productType}_edit`, {id: row.product?.id.toString() || ''});
  const thumbnailUrl = useProductThumbnail(row.product);
  const blueColor = useTheme().color.blue100;

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
        {!isCompact && (
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
        )}
        {!isCompact && (
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
        )}
        <Cell>
          <InputCellContainer>
            <QuantityInput
              title={translate('pim_enrich.entity.product.module.associations.quantified.quantity')}
              type="number"
              min={1}
              max={MAX_QUANTITY}
              value={row.quantifiedLink.quantity}
              isInvalid={0 < filterErrors(row.errors, 'quantity').length}
              onChange={event => {
                const numberValue = Number(event.currentTarget.value) || 1;
                const limitedValue = numberValue > MAX_QUANTITY ? row.quantifiedLink.quantity : numberValue;

                onChange({
                  ...row,
                  quantifiedLink: {...row.quantifiedLink, quantity: limitedValue},
                });
              }}
            />
          </InputCellContainer>
          {formatParameters(row.errors).map((error, key) => (
            <Helper key={key} level="error" inline={true}>
              {translate(error.messageTemplate, error.parameters, error.plural)}
            </Helper>
          ))}
        </Cell>
        {!isCompact ? (
          <Cell>
            <ActionCellContainer>
              <RowActions>
                {undefined !== parentQuantifiedLink &&
                  parentQuantifiedLink.quantity !== row.quantifiedLink.quantity && (
                    <BrokenLinkIcon
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
        ) : (
          <Cell>
            <ActionCellContainer>
              <RowActions>
                {undefined === parentQuantifiedLink && (
                  <RowAction onClick={() => onRemove(row)}>
                    <CloseIcon title={translate('pim_enrich.entity.product.module.associations.remove')} size={20} />
                  </RowAction>
                )}
              </RowActions>
            </ActionCellContainer>
          </Cell>
        )}
      </Container>
    </>
  );
};

export {QuantifiedAssociationRow};
