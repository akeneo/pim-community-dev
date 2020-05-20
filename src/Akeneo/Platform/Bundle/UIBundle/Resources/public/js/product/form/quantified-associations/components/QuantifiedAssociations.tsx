import React, {useState, useEffect, useCallback} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, SearchBar, NoDataSection, NoDataTitle, AssociationTypeIllustration} from '@akeneo-pim-community/shared';
import {
  QuantifiedAssociationCollection,
  Row,
  filterOnLabelOrIdentifier,
  isRowWithProduct,
  addProductToRows,
  getAssociationIdentifiers,
  setRowInCollection,
  isQuantifiedAssociationCollectionEmpty,
  quantifiedAssociationCollectionToRowCollection,
  rowCollectionToQuantifiedAssociationCollection,
  addRowsToCollection,
  removeRowFromCollection,
} from '../models';
import {QuantifiedAssociationRow} from '../components';
import {useProducts} from '../hooks';

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

const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;
  margin-top: 49px;

  > div {
    height: 64px;
  }
`;

const Buttons = styled.div`
  display: flex;
  justify-content: flex-end;
  padding: 10px 0;
`;

type QuantifiedAssociationsProps = {
  value: QuantifiedAssociationCollection;
  associationTypeCode: string;
  onAssociationsChange: (updatedValue: QuantifiedAssociationCollection) => void;
  onOpenPicker: () => Promise<Row[]>;
};

const QuantifiedAssociations = ({
  value,
  associationTypeCode,
  onOpenPicker,
  onAssociationsChange,
}: QuantifiedAssociationsProps) => {
  const translate = useTranslate();
  const [collection, setCollection] = useState<Row[]>(quantifiedAssociationCollectionToRowCollection(value));
  const [searchValue, setSearchValue] = useState('');
  const products = useProducts(getAssociationIdentifiers(collection));
  const collectionWithProducts = addProductToRows(collection, null === products ? [] : products);

  //TODO improve, put only the needed collection in the state
  const filteredCollectionWithProducts = collectionWithProducts
    .filter(row => row.associationTypeCode === associationTypeCode)
    .filter(filterOnLabelOrIdentifier(searchValue));

  useEffect(() => {
    //TODO rework this
    const updatedValue = rowCollectionToQuantifiedAssociationCollection(collection);
    const emptyCollection = Array.isArray(value) && isQuantifiedAssociationCollectionEmpty(updatedValue);
    if (isQuantifiedAssociationCollectionEmpty(updatedValue) && !Array.isArray(value)) {
      onAssociationsChange({});
    } else if (!emptyCollection && updatedValue !== value) {
      onAssociationsChange(updatedValue);
    }
  }, [JSON.stringify(collection)]);

  const handleAdd = useCallback(async () => {
    try {
      const addedRows = await onOpenPicker();
      setCollection(addRowsToCollection(collection, addedRows));
    } catch {
      // We need to catch because the picker has been closed and thus the promise rejected
    }
  }, [JSON.stringify(collection)]);

  const handleRemove = useCallback((row: Row) => setCollection(removeRowFromCollection(collection, row)), [
    JSON.stringify(collection),
  ]);

  const handleChange = useCallback((row: Row) => setCollection(setRowInCollection(collection, row)), [
    JSON.stringify(collection),
  ]);

  return (
    <>
      <SearchBar
        placeholder={translate('pim_enrich.entity.product.module.associations.search.placeholder')}
        count={filteredCollectionWithProducts.length || 0}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      />
      <Buttons>
        <Button color="blue" outline={true} onClick={handleAdd}>
          {translate('pim_enrich.entity.product.module.associations.add_associations')}
        </Button>
      </Buttons>
      {null === products ? (
        <TablePlaceholder className="AknLoadingPlaceHolderContainer">
          {[...Array(collection.length)].map((_, i) => (
            <div key={i} />
          ))}
        </TablePlaceholder>
      ) : 0 === filteredCollectionWithProducts.length ? (
        <NoDataSection>
          <AssociationTypeIllustration size={256} />
          <NoDataTitle>
            {translate(
              '' === searchValue
                ? 'pim_enrich.entity.product.module.associations.no_data'
                : 'pim_enrich.entity.product.module.associations.no_result'
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
              <HeaderCell>{translate('pim_common.completeness')}</HeaderCell>
              <HeaderCell>{translate('pim_enrich.entity.product.module.associations.variant_products')}</HeaderCell>
              <HeaderCell>{translate('pim_enrich.entity.product.module.associations.quantified.quantity')}</HeaderCell>
              <HeaderCell />
            </tr>
          </thead>
          <tbody>
            {filteredCollectionWithProducts.map(row => {
              if (!isRowWithProduct(row)) {
                //TODO use Row instead of RowWithProduct? in QuantifiedAssociationRow loading row
                return;
              }

              return (
                <QuantifiedAssociationRow
                  key={row.product.document_type + row.product.id}
                  row={row}
                  onRemove={handleRemove}
                  onChange={handleChange}
                />
              );
            })}
          </tbody>
        </TableContainer>
      )}
    </>
  );
};

export {QuantifiedAssociations, QuantifiedAssociationsProps};
