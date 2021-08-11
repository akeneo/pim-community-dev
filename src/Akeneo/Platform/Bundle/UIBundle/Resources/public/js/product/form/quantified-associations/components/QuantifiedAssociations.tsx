import React, {useState, useEffect, useCallback} from 'react';
import styled from 'styled-components';
import {BrokenLinkIcon, AssociationTypesIllustration, Helper, Button} from 'akeneo-design-system';
import {
  SearchBar,
  NoDataSection,
  NoDataTitle,
  ValidationError,
  getErrorsForPath,
  formatParameters,
  useTranslate,
  useNotify,
  NotificationLevel,
} from '@akeneo-pim-community/shared';
import {
  Row,
  filterOnLabelOrIdentifier,
  addProductToRows,
  getAssociationIdentifiers,
  updateRowInCollection,
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
  addRowsToCollection,
  removeRowFromCollection,
  QuantifiedAssociation,
  getProductsType,
  newAndUpdatedQuantifiedAssociationsCount,
  hasUpdatedQuantifiedAssociations,
  isQuantifiedAssociationEmpty,
  ProductsType,
} from '../models';
import {QuantifiedAssociationRow} from '../components';
import {useProducts} from '../hooks';

const MAX_LIMIT = 100;

const HeaderCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  top: 44px;
  height: 44px;
  box-shadow: 0 1px 0 ${({theme}) => theme.color.grey120};
  background-color: ${({theme}) => theme.color.white};
  padding-right: 20px;
  white-space: nowrap;

  :first-child {
    padding-left: 20px;
  }
`;

const TableContainer = styled.table`
  width: 100%;
  color: ${({theme}) => theme.color.grey140};
  border-collapse: collapse;
`;

const Buttons = styled.div`
  display: flex;
  justify-content: flex-end;
  padding: 10px 0;
`;

type QuantifiedAssociationsProps = {
  quantifiedAssociations: QuantifiedAssociation;
  parentQuantifiedAssociations: QuantifiedAssociation;
  errors: ValidationError[];
  isCompact?: boolean;
  onAssociationsChange: (quantifiedAssociations: QuantifiedAssociation) => void;
  onOpenPicker: () => Promise<Row[]>;
};

const QuantifiedAssociations = ({
  quantifiedAssociations,
  parentQuantifiedAssociations,
  errors,
  isCompact = false,
  onOpenPicker,
  onAssociationsChange,
}: QuantifiedAssociationsProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const [rowCollection, setRowCollection] = useState<Row[]>(
    quantifiedAssociationToRowCollection(quantifiedAssociations, errors)
  );
  const [searchValue, setSearchValue] = useState('');
  const products = useProducts(getAssociationIdentifiers(rowCollection));
  const collectionWithProducts = addProductToRows(rowCollection, null === products ? [] : products);
  const newAndUpdatedCount = newAndUpdatedQuantifiedAssociationsCount(
    parentQuantifiedAssociations,
    rowCollectionToQuantifiedAssociation(rowCollection)
  );
  const hasUpdatedVariant = hasUpdatedQuantifiedAssociations(
    parentQuantifiedAssociations,
    rowCollectionToQuantifiedAssociation(rowCollection)
  );
  const filteredCollectionWithProducts = collectionWithProducts.filter(filterOnLabelOrIdentifier(searchValue));

  useEffect(() => {
    formatParameters(getErrorsForPath(errors, '')).forEach(error =>
      notify(NotificationLevel.ERROR, translate(error.messageTemplate, error.parameters, error.plural))
    );
    formatParameters(getErrorsForPath(errors, `.${ProductsType.Products}`)).forEach(error =>
      notify(NotificationLevel.ERROR, translate(error.messageTemplate, error.parameters, error.plural))
    );
    formatParameters(getErrorsForPath(errors, `.${ProductsType.ProductModels}`)).forEach(error =>
      notify(NotificationLevel.ERROR, translate(error.messageTemplate, error.parameters, error.plural))
    );
  }, [JSON.stringify(errors)]);

  useEffect(() => {
    setRowCollection(quantifiedAssociationToRowCollection(quantifiedAssociations, errors));
  }, [quantifiedAssociations, JSON.stringify(errors)]);

  useEffect(() => {
    const updatedValue = rowCollectionToQuantifiedAssociation(rowCollection);

    // Early return if both current value and updated value are empty to prevent false-positive unsaved changes
    if (isQuantifiedAssociationEmpty(quantifiedAssociations) && isQuantifiedAssociationEmpty(updatedValue)) return;

    onAssociationsChange(updatedValue);
  }, [JSON.stringify(rowCollection)]);

  const handleAdd = useCallback(async () => {
    try {
      const addedRows = await onOpenPicker();
      setRowCollection(rowCollection => addRowsToCollection(rowCollection, addedRows));
      // We need to catch in case the picker gets closed and the promise rejected
    } catch {}
  }, []);

  const handleRemove = useCallback(
    (row: Row) => setRowCollection(rowCollection => removeRowFromCollection(rowCollection, row)),
    []
  );

  const handleChange = useCallback(
    (row: Row) => setRowCollection(rowCollection => updateRowInCollection(rowCollection, row)),
    []
  );

  return (
    <>
      {MAX_LIMIT <= newAndUpdatedCount && (
        <Helper level="info">
          {translate('pim_enrich.entity.product.module.associations.limit_reached', {maxLimit: MAX_LIMIT.toString()})}
        </Helper>
      )}
      {hasUpdatedVariant && (
        <Helper level="info" icon={<BrokenLinkIcon />}>
          {translate('pim_enrich.entity.product.module.associations.variant_updated')}
        </Helper>
      )}
      <SearchBar
        placeholder={translate('pim_enrich.entity.product.module.associations.search.placeholder')}
        count={filteredCollectionWithProducts.length || 0}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      />
      {!isCompact && (
        <Buttons>
          <Button level="secondary" onClick={handleAdd}>
            {translate('pim_enrich.entity.product.module.associations.add_associations')}
          </Button>
        </Buttons>
      )}
      {null === products ? null : 0 === filteredCollectionWithProducts.length ? (
        <NoDataSection>
          <AssociationTypesIllustration size={256} />
          <NoDataTitle>
            {translate(
              '' === searchValue
                ? 'pim_enrich.entity.product.module.associations.no_data'
                : 'pim_common.no_search_result'
            )}
          </NoDataTitle>
        </NoDataSection>
      ) : (
        <TableContainer>
          <thead>
            <tr>
              <HeaderCell>{translate('pim_common.image')}</HeaderCell>
              <HeaderCell>{translate('pim_common.label')}</HeaderCell>
              <HeaderCell>{translate('pim_common.identifier')}</HeaderCell>
              {!isCompact && <HeaderCell>{translate('pim_common.completeness')}</HeaderCell>}
              {!isCompact && (
                <HeaderCell>{translate('pim_enrich.entity.product.module.associations.variant_products')}</HeaderCell>
              )}
              <HeaderCell>{translate('pim_enrich.entity.product.module.associations.quantified.quantity')}</HeaderCell>
              <HeaderCell />
            </tr>
          </thead>
          <tbody>
            {filteredCollectionWithProducts.map((row, index) => (
              <QuantifiedAssociationRow
                key={index}
                row={row}
                isCompact={isCompact}
                parentQuantifiedLink={parentQuantifiedAssociations[getProductsType(row.productType)].find(
                  quantifiedAssociation => quantifiedAssociation.identifier === row.quantifiedLink.identifier
                )}
                onRemove={handleRemove}
                onChange={handleChange}
              />
            ))}
          </tbody>
        </TableContainer>
      )}
    </>
  );
};

export {QuantifiedAssociations, QuantifiedAssociationsProps};
