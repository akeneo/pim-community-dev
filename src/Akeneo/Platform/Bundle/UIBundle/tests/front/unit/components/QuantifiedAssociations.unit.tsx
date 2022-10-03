import React from 'react';
import {act, getByText, fireEvent, queryByText, getAllByTitle} from '@testing-library/react';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {QuantifiedAssociations} from '../../../../Resources/public/js/product/form/quantified-associations/components/QuantifiedAssociations';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';
import {queryByDisplayValue} from '@testing-library/dom';
import {renderDOMWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

jest.mock('pimui/js/product/form/quantified-associations/hooks/useProducts.ts', () => ({
  useProducts: (identifiers: {products: string[]; product_models: string[]}) => {
    if (0 === identifiers.products.length) return [];
    if ('null' === identifiers.products[0]) return null;

    return [
      {
        id: '3fa79b52-5900-49e8-a197-1181f58ec3cb',
        identifier: 'bag',
        label: 'Nice bag',
        document_type: 'product',
        image: null,
        completeness: 100,
        variant_product_completenesses: null,
      },
      {
        id: 2,
        identifier: 'braided-hat',
        label: 'Braided hat',
        document_type: 'product_model',
        image: {filePath: '/some.jpg'},
        completeness: null,
        variant_product_completenesses: {
          completeChildren: 1,
          totalChildren: 2,
        },
      },
    ];
  },
}));

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

const quantifiedAssociationCollection = {
  products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 3}],
  product_models: [{identifier: 'braided-hat', quantity: 15}],
};

test('It displays quantified association rows for a quantified association collection', async () => {
  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        parentQuantifiedAssociations={{
          products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 1}],
          product_models: [{identifier: 'braided-hat', quantity: 12}],
        }}
        errors={[]}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  const quantityInputs = getAllByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement[];

  expect(getByText(container, 'Nice bag')).toBeInTheDocument();
  expect(getAllByTitle(container, 'pim_enrich.entity.product.module.associations.quantified.unlinked')).toHaveLength(2);
  expect(queryByText(container, 'Braided hat')).toBeInTheDocument();
  expect(quantityInputs[0].value).toBe('3');
  expect(quantityInputs[1].value).toBe('15');
  expect(quantityInputs.length).toBe(2);
});

test('It displays no rows and a no data information when the quantified association collection is empty', async () => {
  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={{products: [], product_models: []}}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        errors={[]}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  expect(queryByText(container, 'Nice bag')).not.toBeInTheDocument();
  expect(queryByText(container, 'Braided hat')).not.toBeInTheDocument();
  expect(getByText(container, 'pim_enrich.entity.product.module.associations.no_data')).toBeInTheDocument();
});

test('It triggers the onChange event when a quantity is changed', async () => {
  const onChange = jest.fn();

  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        errors={[]}
        onAssociationsChange={onChange}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  const quantityInputs = getAllByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement[];

  fireEvent.change(quantityInputs[0], {target: {value: '16'}});

  expect(onChange).toBeCalledWith({
    products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 16}],
    product_models: [{identifier: 'braided-hat', quantity: 15}],
  });
});

test('It triggers the onRowDelete event when the remove button is clicked', async () => {
  const onChange = jest.fn();

  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        errors={[]}
        onAssociationsChange={onChange}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  const removeButton = getAllByTitle(container, 'pim_enrich.entity.product.module.associations.remove');
  fireEvent.click(removeButton[0]);

  expect(onChange).toBeCalledWith({
    products: [],
    product_models: [{identifier: 'braided-hat', quantity: 15}],
  });
});

test('It adds products when the user confirm the picker', async () => {
  const onChange = jest.fn();

  const smallQuantifiedAssociationCollection = {
    products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 3}],
    product_models: [],
  };

  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={smallQuantifiedAssociationCollection}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        onAssociationsChange={onChange}
        errors={[]}
        onOpenPicker={() =>
          Promise.resolve([
            {
              productType: ProductType.ProductModel,
              quantifiedLink: {identifier: 'braided-hat', quantity: 1},
              product: null,
              errors: [],
            },
            {
              productType: ProductType.Product,
              quantifiedLink: {uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 1},
              product: null,
              errors: [],
            },
          ])
        }
      />,
      container
    );
  });

  expect(queryByDisplayValue(container, '3')).toBeInTheDocument();
  expect(queryByText(container, 'Nice bag')).toBeInTheDocument();

  await act(async () => {
    const addButton = getByText(container, 'pim_enrich.entity.product.module.associations.add_associations');
    fireEvent.click(addButton);
  });

  expect(queryByText(container, '3')).not.toBeInTheDocument();
  expect(queryByText(container, 'Nice bag')).toBeInTheDocument();
  expect(queryByText(container, 'Braided hat')).toBeInTheDocument();
});

test('It displays no table rows when the quantified association collection is null', async () => {
  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={{
          //The useProducts mock defined above will simulate loading thanks to the 'null' identifier
          products: [{uuid: 'null', quantity: 2}],
          product_models: [],
        }}
        errors={[]}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  expect(container.querySelector('table')).toBe(null);
});

test('It notifies when a root error is detected', async () => {
  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        errors={[
          {
            propertyPath: '',
            message: 'an error occured',
            messageTemplate: 'an.error.occured',
            invalidValue: '',
            parameters: {},
          },
        ]}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  expect(dependencies.notify).toBeCalledWith('error', 'an.error.occured');
});

test('It notifies when a product error is detected', async () => {
  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        errors={[
          {
            propertyPath: '.products',
            message: 'a product error occured',
            messageTemplate: 'a.product.error.occured',
            invalidValue: '',
            parameters: {},
          },
        ]}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  expect(dependencies.notify).toBeCalledWith('error', 'a.product.error.occured');
});

test('It notifies when a product model error is detected', async () => {
  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        errors={[
          {
            propertyPath: '.product_models',
            message: 'a product model error occured',
            messageTemplate: 'a.product.model.error.occured',
            invalidValue: '',
            parameters: {},
          },
        ]}
        parentQuantifiedAssociations={{products: [], product_models: []}}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  expect(dependencies.notify).toBeCalledWith('error', 'a.product.model.error.occured');
});

/**
 * @see https://akeneo.atlassian.net/browse/PIM-10515
 */
test('It does not display the add association button if the user is not owner of the product', async () => {
  jest.mock('pim/security-context', () => {}, {virtual: true});

  await act(async () => {
    renderDOMWithProviders(
      <QuantifiedAssociations
        quantifiedAssociations={quantifiedAssociationCollection}
        parentQuantifiedAssociations={{
          products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 1}],
          product_models: [],
        }}
        errors={[]}
        isUserOwner={false}
        onAssociationsChange={jest.fn()}
        onOpenPicker={jest.fn()}
      />,
      container
    );
  });

  expect(
    queryByText(container, 'pim_enrich.entity.product.module.associations.add_associations')
  ).not.toBeInTheDocument();
});
