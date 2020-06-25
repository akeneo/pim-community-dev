import React from 'react'
import { ClearCategoriesAction } from '../../../../../../src/models/actions';
import { renderWithProviders } from '../../../../../../test-utils';
import { ClearCategoriesActionLine } from '../../../../../../src/pages/EditRules/components/actions/ClearCategoriesActionLine';
import { locales, scopes } from '../../../../factories'

const action: ClearCategoriesAction = {
  type: 'clear',
  field: 'categories',
}

describe('ClearCategoriesActionLine', () => {
  it('should display the clear categories action line', async() => {
    const { findByText } = renderWithProviders(
      <ClearCategoriesActionLine
        currentCatalogLocale={'en_US'}
        lineNumber={1}
        action={action}
        handleDelete={() => {}}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    )

    expect(await findByText('pimee_catalog_rule.form.edit.actions.clear_categories.title')).toBeInTheDocument();
    expect(await findByText('pimee_catalog_rule.form.helper.clear_categories')).toBeInTheDocument()
  })
})
