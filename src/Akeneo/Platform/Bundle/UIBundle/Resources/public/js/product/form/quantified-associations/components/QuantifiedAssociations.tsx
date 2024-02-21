import React, {useCallback, useEffect, useRef, useState} from 'react';
import {
  AssociationTypesIllustration,
  BrokenLinkIcon,
  Button,
  Helper,
  Placeholder,
  Search,
  Table,
  useAutoFocus,
} from 'akeneo-design-system';
import {
  formatParameters,
  getErrorsForPath,
  NotificationLevel,
  useNotify,
  useSecurity,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {
  addProductToRows,
  addRowsToCollection,
  filterOnLabelOrIdentifier,
  getAssociationIdentifiers,
  getProductsType,
  getQuantifiedLinkIdentifier,
  hasUpdatedQuantifiedAssociations,
  isQuantifiedAssociationEmpty,
  newAndUpdatedQuantifiedAssociationsCount,
  ProductsType,
  QuantifiedAssociation,
  quantifiedAssociationToRowCollection,
  removeRowFromCollection,
  Row,
  rowCollectionToQuantifiedAssociation,
  updateRowInCollection,
} from '../models';
import {QuantifiedAssociationRow} from '../components';
import {useProducts} from '../hooks';

const MAX_LIMIT = 100;

type QuantifiedAssociationsProps = {
  quantifiedAssociations: QuantifiedAssociation;
  parentQuantifiedAssociations: QuantifiedAssociation;
  errors: ValidationError[];
  isUserOwner?: boolean;
  isCompact?: boolean;
  onAssociationsChange: (quantifiedAssociations: QuantifiedAssociation) => void;
  onOpenPicker: () => Promise<Row[]>;
};

const QuantifiedAssociations = ({
  quantifiedAssociations,
  parentQuantifiedAssociations,
  errors,
  isCompact = false,
  isUserOwner = true,
  onOpenPicker,
  onAssociationsChange,
}: QuantifiedAssociationsProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const notify = useNotify();
  const [rowCollection, setRowCollection] = useState<Row[]>(
    quantifiedAssociationToRowCollection(quantifiedAssociations, errors)
  );
  const [searchValue, setSearchValue] = useState('');
  const products = useProducts(getAssociationIdentifiers(rowCollection));
  const collectionWithProducts = addProductToRows(rowCollection, products);
  const newAndUpdatedCount = newAndUpdatedQuantifiedAssociationsCount(
    parentQuantifiedAssociations,
    rowCollectionToQuantifiedAssociation(rowCollection)
  );
  const hasUpdatedVariant = hasUpdatedQuantifiedAssociations(
    parentQuantifiedAssociations,
    rowCollectionToQuantifiedAssociation(rowCollection)
  );
  const filteredCollectionWithProducts = collectionWithProducts.filter(filterOnLabelOrIdentifier(searchValue));
  const inputRef = useRef<HTMLInputElement>(null);
  const canAddAssociation = isGranted('pim_enrich_associations_edit') && isUserOwner;

  useAutoFocus(inputRef);

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
      <Search
        placeholder={translate('pim_enrich.entity.product.module.associations.search.placeholder')}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
        inputRef={inputRef}
      >
        <Search.ResultCount>
          {translate(
            'pim_common.result_count',
            {itemsCount: filteredCollectionWithProducts.length || 0},
            filteredCollectionWithProducts.length || 0
          )}
        </Search.ResultCount>
        {canAddAssociation && (
          <>
            <Search.Separator />
            <Button level="secondary" onClick={handleAdd}>
              {translate('pim_enrich.entity.product.module.associations.add_associations')}
            </Button>
          </>
        )}
      </Search>
      {null === products ? null : 0 === filteredCollectionWithProducts.length ? (
        <Placeholder
          illustration={<AssociationTypesIllustration />}
          size="large"
          title={translate(
            '' === searchValue ? 'pim_enrich.entity.product.module.associations.no_data' : 'pim_common.no_search_result'
          )}
        />
      ) : (
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_common.image')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.identifier')}</Table.HeaderCell>
            {!isCompact && <Table.HeaderCell>{translate('pim_common.completeness')}</Table.HeaderCell>}
            {!isCompact && (
              <Table.HeaderCell>
                {translate('pim_enrich.entity.product.module.associations.variant_products')}
              </Table.HeaderCell>
            )}
            <Table.HeaderCell>
              {translate('pim_enrich.entity.product.module.associations.quantified.quantity')}
            </Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.actions')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {filteredCollectionWithProducts.map((row, index) => (
              <QuantifiedAssociationRow
                key={index}
                row={row}
                isUserOwner={isUserOwner}
                isCompact={isCompact}
                parentQuantifiedLink={
                  getProductsType(row.productType) === ProductsType.Products
                    ? parentQuantifiedAssociations.products.find(
                        quantifiedLink =>
                          getQuantifiedLinkIdentifier(quantifiedLink) ===
                          getQuantifiedLinkIdentifier(row.quantifiedLink)
                      )
                    : parentQuantifiedAssociations.product_models.find(
                        quantifiedLink =>
                          getQuantifiedLinkIdentifier(quantifiedLink) ===
                          getQuantifiedLinkIdentifier(row.quantifiedLink)
                      )
                }
                onRemove={handleRemove}
                onChange={handleChange}
              />
            ))}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

export {QuantifiedAssociations, QuantifiedAssociationsProps};
