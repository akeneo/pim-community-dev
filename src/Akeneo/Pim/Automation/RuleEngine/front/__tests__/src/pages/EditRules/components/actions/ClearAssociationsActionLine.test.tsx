import React from 'react';
import { renderWithProviders } from '../../../../../../test-utils';
import { ClearAssociationsActionLine } from '../../../../../../src/pages/EditRules/components/actions/ClearAssociationsActionLine';
import { locales, scopes } from '../../../../factories';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher.ts');
jest.mock('../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector');
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('ClearAssociationsActionLine', () => {
  it('should display the clear associations action line', async () => {
    const {
      findByText,
      findAllByText,
    } = renderWithProviders(
      <ClearAssociationsActionLine
        currentCatalogLocale={'en_US'}
        lineNumber={1}
        handleDelete={jest.fn()}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    );

    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.actions.clear_associations.title'
      )
    ).toBeInTheDocument();
    expect(
      await findAllByText('pimee_catalog_rule.form.helper.clear_associations')
    ).toHaveLength(2);
  });
});
