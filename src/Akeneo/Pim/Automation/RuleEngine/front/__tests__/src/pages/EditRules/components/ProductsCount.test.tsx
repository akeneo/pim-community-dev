import React from 'react';
import {renderWithProviders} from '../../../../../test-utils';
import {Status} from '../../../../../src/rules.constants';
import {ProductsCount} from '../../../../../src/pages/EditRules/components/ProductsCount';

describe('ProductsCount', () => {
  test('it should render in complete mode', () => {
    // Given
    const count = 10;
    const status = Status.COMPLETE;
    // When
    const {getByText} = renderWithProviders(
      <ProductsCount count={count} status={status} />,
      {legacy: true}
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.complete')
    ).toBeInTheDocument();
  });
  test('it should render in error mode', () => {
    // Given
    const count = -1;
    const status = Status.ERROR;
    // When
    const {getByText} = renderWithProviders(
      <ProductsCount count={count} status={status} />,
      {legacy: true}
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.error')
    ).toBeInTheDocument();
  });
  test('it should render in pending mode', () => {
    // Given
    const count = -1;
    const status = Status.PENDING;
    // When
    const {getByText} = renderWithProviders(
      <ProductsCount count={count} status={status} />,
      {legacy: true}
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.pending')
    ).toBeInTheDocument();
  });
});
