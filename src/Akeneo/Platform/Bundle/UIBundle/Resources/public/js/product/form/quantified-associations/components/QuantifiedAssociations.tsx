import React, {useState, useEffect, useCallback} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, SearchBar, NoDataSection, NoDataTitle, AssociationTypeIllustration} from '@akeneo-pim-community/shared';
import {
  Row,
  filterOnLabelOrIdentifier,
  addProductToRows,
  getAssociationIdentifiers,
  setRowInCollection,
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
  addRowsToCollection,
  removeRowFromCollection,
  QuantifiedAssociation,
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

const Buttons = styled.div`
  display: flex;
  justify-content: flex-end;
  padding: 10px 0;
`;

type QuantifiedAssociationsProps = {
  quantifiedAssociations: QuantifiedAssociation;
  associationTypeCode: string;
  onAssociationsChange: (quantifiedAssociations: QuantifiedAssociation) => void;
  onOpenPicker: () => Promise<Row[]>;
};

const QuantifiedAssociations = ({
  quantifiedAssociations,
  associationTypeCode,
  onOpenPicker,
  onAssociationsChange,
}: QuantifiedAssociationsProps) => {
  const translate = useTranslate();
  const [rowCollection, setRowCollection] = useState<Row[]>([]);
  const [searchValue, setSearchValue] = useState('');
  const products = useProducts(getAssociationIdentifiers(rowCollection));
  const collectionWithProducts = addProductToRows(rowCollection, null === products ? [] : products);

  const filteredCollectionWithProducts = collectionWithProducts.filter(filterOnLabelOrIdentifier(searchValue));

  useEffect(() => {
    setRowCollection(quantifiedAssociationToRowCollection(quantifiedAssociations));
  }, [associationTypeCode]);

  useEffect(() => {
    //TODO check with empty value
    const updatedValue = rowCollectionToQuantifiedAssociation(rowCollection);
    onAssociationsChange(updatedValue);
  }, [JSON.stringify(rowCollection)]);

  const handleAdd = useCallback(async () => {
    try {
      const addedRows = await onOpenPicker();
      setRowCollection(addRowsToCollection(rowCollection, addedRows));
      // We need to catch in case the picker gets closed and the promise rejected
    } catch {}
  }, [JSON.stringify(rowCollection)]);

  const handleRemove = useCallback((row: Row) => setRowCollection(removeRowFromCollection(rowCollection, row)), [
    JSON.stringify(rowCollection),
  ]);

  const handleChange = useCallback((row: Row) => setRowCollection(setRowInCollection(rowCollection, row)), [
    JSON.stringify(rowCollection),
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
      {null === products ? null : 0 === filteredCollectionWithProducts.length ? (
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
            {filteredCollectionWithProducts.map((row, index) => (
              <QuantifiedAssociationRow key={index} row={row} onRemove={handleRemove} onChange={handleChange} />
            ))}
          </tbody>
        </TableContainer>
      )}
    </>
  );
};

export {QuantifiedAssociations, QuantifiedAssociationsProps};
