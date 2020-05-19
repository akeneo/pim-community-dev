import React, {useState, useEffect, useCallback} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, SearchBar, NoDataSection, NoDataTitle, AssociationTypeIllustration} from '@akeneo-pim-community/shared';
import {
  QuantifiedAssociationCollection,
  setQuantifiedAssociationCollection,
  getProductsType,
  ProductsType,
  ProductType,
  QuantifiedLink,
  Row,
  filterOnLabelOrIdentifier,
  isRowWithProduct,
} from '../models';
import {QuantifiedAssociationRow} from '.';
import {useProducts, getAssociationIdentifiers, addProductToRows} from '../hooks';

const HeaderCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  top: 44px;
  height: 44px;
  box-shadow: 0 1px 0 ${props => props.theme.color.grey120};
  background-color: ${props => props.theme.color.white};
  padding-right: 20px;
  white-space: nowrap;

  :first-child {
    padding-left: 20px;
  }
`;

const TableContainer = styled.table`
  width: 100%;
  color: ${props => props.theme.color.grey140};
  border-collapse: collapse;
`;

const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
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

const quantifiedAssociationCollectionToRowCollection = (collection: QuantifiedAssociationCollection): Row[] => {
  return Object.keys(collection).reduce((result: Row[], associationTypeCode) => {
    const products = collection[associationTypeCode].products || [];
    const productModels = collection[associationTypeCode].product_models || [];

    return [
      ...result,
      ...products.map(({identifier, quantity}) => ({
        associationTypeCode,
        identifier,
        quantity,
        productType: ProductType.Product,
        product: null,
      })),
      ...productModels.map(({identifier, quantity}) => ({
        associationTypeCode,
        identifier,
        quantity,
        productType: ProductType.ProductModel,
        product: null,
      })),
    ];
  }, []);
};

const rowCollectionToQuantifiedAssociationCollection = (rows: Row[]): QuantifiedAssociationCollection => {
  return rows.reduce(
    (
      quantifiedAssociationCollection: QuantifiedAssociationCollection,
      {productType, associationTypeCode, identifier, quantity}: Row
    ): QuantifiedAssociationCollection => {
      if (!(associationTypeCode in quantifiedAssociationCollection)) {
        quantifiedAssociationCollection[associationTypeCode] = {
          [ProductsType.Products]: [],
          [ProductsType.ProductModels]: [],
        };
      }

      quantifiedAssociationCollection[associationTypeCode][getProductsType(productType)].push({
        identifier,
        quantity,
      });

      return quantifiedAssociationCollection;
    },
    {}
  );
};

const isQuantifiedAssociationCollectionEmpty = (value: QuantifiedAssociationCollection) =>
  !Object.values(value).some(
    quantifiedAssociation =>
      quantifiedAssociation.products.length !== 0 || quantifiedAssociation.product_models.length !== 0
  );

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

  const filteredCollectionWithProducts = collectionWithProducts.filter(filterOnLabelOrIdentifier(searchValue));

  useEffect(() => {
    const updatedValue = rowCollectionToQuantifiedAssociationCollection(collection);
    const emptyCollection = Array.isArray(value) && isQuantifiedAssociationCollectionEmpty(updatedValue);
    if (isQuantifiedAssociationCollectionEmpty(updatedValue) && !Array.isArray(value)) {
      onAssociationsChange({});
    } else if (!emptyCollection && updatedValue !== value) {
      onAssociationsChange(updatedValue);
    }
  }, [collection]);

  const onRowDelete = useCallback(
    ({identifier, productType}: Row) => {
      const updatedCollection = collection.filter(
        row =>
          row.identifier !== identifier ||
          row.associationTypeCode !== associationTypeCode ||
          row.productType !== productType
      );

      setCollection(updatedCollection);
    },
    [collection, associationTypeCode]
  );

  const onAddAssociation = useCallback(async () => {
    const addedRows = await onOpenPicker();

    const mergedRows = addedRows.reduce(
      (collection: Row[], addedRow: Row) => {
        const row = collection.find(
          row =>
            addedRow.identifier === row.identifier &&
            addedRow.productType === row.productType &&
            addedRow.associationTypeCode === row.associationTypeCode
        );

        if (undefined !== row) {
          row.quantity = 1;
        } else {
          collection.push(addedRow);
        }

        return collection;
      },
      [...collection]
    );

    setCollection(mergedRows);
  }, [JSON.stringify(collection)]);

  const onRowChange = useCallback(
    (quantifiedLink: QuantifiedLink, row: Row) => {
      setCollection(
        setQuantifiedAssociationCollection(collection, associationTypeCode, row.productType, quantifiedLink)
      );
    },
    [JSON.stringify(collection), associationTypeCode]
  );

  return (
    <>
      <SearchBar
        placeholder={translate('pim_enrich.entity.product.module.associations.search.placeholder')}
        count={filteredCollectionWithProducts.length || 0}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      />
      <Buttons>
        <Button color="blue" outline={true} onClick={onAddAssociation}>
          {translate('pim_enrich.entity.product.module.associations.add_associations')}
        </Button>
      </Buttons>
      {null === products ? (
        <TablePlaceholder className={`AknLoadingPlaceHolderContainer`}>
          {[...Array(collection.length)].map((_, i) => (
            <div key={i} />
          ))}
        </TablePlaceholder>
      ) : 0 === filteredCollectionWithProducts.length ? (
        '' === searchValue ? (
          <NoDataSection>
            <AssociationTypeIllustration size={256} />
            <NoDataTitle>{translate('pim_enrich.entity.product.module.associations.no_data')}</NoDataTitle>
          </NoDataSection>
        ) : (
          <NoDataSection>
            <AssociationTypeIllustration size={256} />
            <NoDataTitle>{translate('pim_enrich.entity.product.module.associations.no_result')}</NoDataTitle>
          </NoDataSection>
        )
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
                // loading row
                return;
              }

              return (
                <QuantifiedAssociationRow
                  key={row.product.document_type + row.product.id}
                  onRowDelete={onRowDelete}
                  row={row}
                  onChange={quantifiedLink => onRowChange(quantifiedLink, row)}
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
