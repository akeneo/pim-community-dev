import React from 'react';
import {renderWithProviders} from '../../../../../../test-utils';
import {ClearCategoriesActionLine} from '../../../../../../src/pages/EditRules/components/actions/ClearCategoriesActionLine';
import {locales, scopes, uiLocales} from '../../../../factories';

describe('ClearCategoriesActionLine', () => {
  it('should display the clear categories action line', async () => {
    const {
      findByText,
      findAllByText,
    } = renderWithProviders(
      <ClearCategoriesActionLine
        currentCatalogLocale={'en_US'}
        lineNumber={1}
        handleDelete={jest.fn()}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
      />,
      {all: true}
    );

    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.actions.clear_categories.title'
      )
    ).toBeInTheDocument();
    expect(
      await findAllByText('pimee_catalog_rule.form.helper.clear_categories')
    ).toHaveLength(2);
  });
});
