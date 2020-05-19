import React, {useState, useEffect} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, SearchBar, NoDataSection, NoDataTitle, AssociationTypeIllustration} from '@akeneo-pim-community/shared';
import {
  Identifier,
  QuantifiedAssociationCollection,
  getQuantifiedAssociationCollectionIdentifiers,
  getQuantifiedLinkForIdentifier,
  setQuantifiedAssociationCollection,
  getProductsType,
} from '../models';
import {QuantifiedAssociationRow} from '.';
import {useProducts} from '../hooks';

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

const filterOnLabelOrIdentifier = (searchValue: string) => (entity: {label: string; identifier: Identifier}): boolean =>
  -1 !== entity.label.toLowerCase().indexOf(searchValue.toLowerCase()) ||
  (undefined !== entity.identifier && -1 !== entity.identifier.toLowerCase().indexOf(searchValue.toLowerCase()));

type QuantifiedAssociationsProps = {
  value: QuantifiedAssociationCollection;
  associationTypeCode: string;
  onAssociationsChange: (updatedValue: QuantifiedAssociationCollection) => void;
  onOpenPicker: () => void;
};

const QuantifiedAssociations = ({
  value,
  associationTypeCode,
  onOpenPicker,
  onAssociationsChange,
}: QuantifiedAssociationsProps) => {
  const translate = useTranslate();
  const [collection, setCollection] = useState<QuantifiedAssociationCollection>(value);
  const [searchValue, setSearchValue] = useState('');
  const identifiers = getQuantifiedAssociationCollectionIdentifiers(value, associationTypeCode);
  const products = useProducts(identifiers);

  const filteredProducts = null === products ? null : products.filter(filterOnLabelOrIdentifier(searchValue));

  useEffect(() => {
    if (collection !== value) {
      onAssociationsChange(collection);
    }
  }, [collection]);

  return (
    <>
      <SearchBar
        placeholder={translate('pim_enrich.entity.product.module.associations.search.placeholder')}
        count={filteredProducts?.length || 0}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      />
      <Buttons>
        <Button color="blue" outline={true} onClick={onOpenPicker}>
          {translate('pim_enrich.entity.product.module.associations.add_associations')}
        </Button>
      </Buttons>
      {null === filteredProducts ? (
        <TablePlaceholder className={`AknLoadingPlaceHolderContainer`}>
          {[...Array(5)].map((_, i) => (
            <div key={i} />
          ))}
        </TablePlaceholder>
      ) : 0 === filteredProducts.length ? (
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
            {filteredProducts.map(product => (
              <QuantifiedAssociationRow
                key={product.document_type + product.id}
                product={product}
                quantifiedLink={getQuantifiedLinkForIdentifier(
                  collection,
                  associationTypeCode,
                  getProductsType(product.document_type),
                  product.identifier
                )}
                onChange={quantifiedLink =>
                  setCollection(
                    setQuantifiedAssociationCollection(
                      collection,
                      associationTypeCode,
                      getProductsType(product.document_type),
                      quantifiedLink
                    )
                  )
                }
              />
            ))}
          </tbody>
        </TableContainer>
      )}
    </>
  );
};

export {QuantifiedAssociations, QuantifiedAssociationsProps};
