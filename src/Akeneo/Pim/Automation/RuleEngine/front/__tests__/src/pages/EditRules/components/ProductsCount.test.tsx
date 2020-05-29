import React from 'react';
import { render } from '../../../../../test-utils';
import { Status } from '../../../../../src/rules.constants';
import { Translate } from '../../../../../src/dependenciesTools';
import { ProductsCount } from '../../../../../src/pages/EditRules/components/ProductsCount';

describe('ProductsCount', () => {
  test('it should render in complete mode', () => {
    // Given
    const count = '10';
    const status = Status.COMPLETE;
    const translate: Translate = (
      str: string,
      params?: { [key: string]: string | number }
    ) => `${str} ${params?.count}`;
    // When
    const { getByText } = render(
      <ProductsCount count={count} status={status} translate={translate} />
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.complete 10')
    ).toBeInTheDocument();
  });
  test('it should render in error mode', () => {
    // Given
    const count = '-1';
    const status = Status.ERROR;
    const translate: Translate = (str: string) => str;
    // When
    const { getByText } = render(
      <ProductsCount count={count} status={status} translate={translate} />
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.error')
    ).toBeInTheDocument();
  });
  test('it should render in pending mode', () => {
    // Given
    const count = '-1';
    const status = Status.PENDING;
    const translate: Translate = (str: string) => str;
    // When
    const { getByText } = render(
      <ProductsCount count={count} status={status} translate={translate} />
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.pending')
    ).toBeInTheDocument();
  });
});
