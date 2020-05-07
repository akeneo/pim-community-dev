import React, {useState, useEffect} from 'react';
import styled from 'styled-components';
import {Button, SearchBar, AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {DependenciesProvider, useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Identifier = string;

type QuantifiedLink = {
  identifier: Identifier;
  quantity: string;
};

type QuantifiedAssociationCollection = {
  [associationTypeCode: string]: {
    products: QuantifiedLink[];
    product_models: QuantifiedLink[];
  };
};

const getQuantifiedAssociationCollectionIdentifiers = (
  quantifiedAssociationCollection: QuantifiedAssociationCollection,
  associationTypeCode: string
): Identifier[] => {
  const productIdentifiers = quantifiedAssociationCollection[associationTypeCode].products.map(
    ({identifier}) => identifier
  );
  const productModelIdentifiers = quantifiedAssociationCollection[associationTypeCode].product_models.map(
    ({identifier}) => identifier
  );

  return [...productIdentifiers, ...productModelIdentifiers];
};

const filterOnLabelOrIdentifier = (searchValue: string) => (entity: {label: string; identifier: Identifier}): boolean =>
  -1 !== entity.label.toLowerCase().indexOf(searchValue.toLowerCase()) ||
  (undefined !== entity.identifier && -1 !== entity.identifier.toLowerCase().indexOf(searchValue.toLowerCase()));

type QuantifiedAssociationsProps = {
  value: QuantifiedAssociationCollection;
  associationType: string;
  onAssociationsChange: (updatedValue: QuantifiedAssociationCollection) => void;
  onOpenPicker: () => void;
};

type Product = {
  id: number;
  identifier: string;
  label: string;
  document_type: string;
  image: any;
  completeness: number | null;
  variant_product_completenesses: any;
};

const productFetcher = async (identifiers: Identifier[]): Promise<Product[]> => {
  const response = await Promise.resolve({
    items: [
      {
        id: 46,
        identifier: 'model-braided-hat',
        label: 'Braided hat ',
        document_type: 'product_model',
        image: {
          filePath: 'e/3/c/9/e3c9304b2ca79802a7dd05c0d3345cdfdda397dc_braided_hat.jpg',
          originalFilename: 'braided-hat.jpg',
        },
        completeness: null,
        variant_product_completenesses: {completeChildren: 0, totalChildren: 2},
      },
      {
        id: 1078,
        identifier: '8009612',
        label: 'Kodak i2400',
        document_type: 'product',
        image: null,
        completeness: 60,
        variant_product_completenesses: null,
      },
    ],
    total_count: 2,
  });

  console.log(identifiers);

  return response.items;
};

const useProducts = (identifiers: Identifier[]) => {
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    (async () => {
      setProducts(await productFetcher(identifiers));
    })();
  }, [JSON.stringify(identifiers)]);

  return products;
};

const RowContainer = styled.tr`
  height: 74px;
  border-bottom: 1px solid ${props => props.theme.color.grey70};
  padding: 0 15px;

  :hover {
    background-color: ${props => props.theme.color.grey60};
  }
`;

const Thumbnail = styled.img`
  width: 44px;
  height: 44px;
  border: 1px solid ${({theme}) => theme.color.grey80};
`;

const HeaderCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  top: 44px;
  height: 44px;
  box-shadow: 0 1px 0 ${props => props.theme.color.grey120};
  background-color: ${props => props.theme.color.white};

  :first-child {
    padding-left: 20px;
  }
`;

const LabelCell = styled.td<{isProduct: boolean}>`
  font-style: italic;
  font-weight: bold;
  color: ${({theme, isProduct}) => (isProduct ? theme.color.purple100 : 'inherit')};
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

type RowProps = {
  product: Product;
};

const Row = ({product}: RowProps) => {
  const __ = useTranslate();
  const isProduct = 'product' === product.document_type;

  return (
    <RowContainer>
      <td>
        <Thumbnail src={null !== product.image ? product.image.filePath : '/bundles/pimui/img/image_default.png'} />
      </td>
      <LabelCell isProduct={isProduct}>{product.label}</LabelCell>
      <td>{product.identifier}</td>
      <td>
        <Badge>{product.completeness || 0}%</Badge>
      </td>
      <td>
        {null === product.variant_product_completenesses ? (
          __('pim_common.not_available')
        ) : (
          <Badge>
            {product.variant_product_completenesses.completeChildren} /{' '}
            {product.variant_product_completenesses.totalChildren}
          </Badge>
        )}
      </td>
      <td>
        <QuantityInput type="number" defaultValue={1} />
      </td>
    </RowContainer>
  );
};

const TableContainer = styled.table`
  width: 100%;
  color: ${props => props.theme.color.grey140};
  border-collapse: collapse;
`;

const Buttons = styled.div`
  display: flex;
  justify-content: flex-end;
  padding: 10px 0;
`;

const Panel = ({value, associationType, onOpenPicker}: QuantifiedAssociationsProps) => {
  const __ = useTranslate();
  const [searchValue, setSearchValue] = useState('');

  const identifiers = getQuantifiedAssociationCollectionIdentifiers(value, associationType);
  const products = useProducts(identifiers);

  const filteredProducts = products.filter(filterOnLabelOrIdentifier(searchValue));

  return (
    <>
      <SearchBar
        placeholder={__('pim_enrich.entity.product.module.associations.search.placeholder')}
        count={filteredProducts.length}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      />
      <Buttons>
        <Button color="blue" outline={true} onClick={onOpenPicker}>
          {__('pim_enrich.entity.product.module.associations.add_associations')}
        </Button>
      </Buttons>
      <TableContainer>
        <thead>
          <tr>
            <HeaderCell>{__('pim_common.image')}</HeaderCell>
            <HeaderCell>{__('pim_common.label')}</HeaderCell>
            <HeaderCell>{__('pim_common.identifier')}</HeaderCell>
            <HeaderCell>{__('pim_common.completeness')}</HeaderCell>
            <HeaderCell>{__('pim_enrich.entity.product.module.associations.variant_products')}</HeaderCell>
            <HeaderCell>{__('pim_enrich.entity.product.module.associations.quantified.quantity')}</HeaderCell>
          </tr>
        </thead>
        <tbody>
          {filteredProducts.map(product => (
            <Row key={product.id} product={product} />
          ))}
        </tbody>
      </TableContainer>
    </>
  );
};

const QuantifiedAssociations = (props: QuantifiedAssociationsProps) => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        <Panel {...props} />
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

export {QuantifiedAssociations};
