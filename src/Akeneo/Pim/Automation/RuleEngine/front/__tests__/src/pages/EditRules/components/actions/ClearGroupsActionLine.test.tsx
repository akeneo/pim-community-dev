import React from 'react';
import { renderWithProviders } from '../../../../../../test-utils';
import { ClearGroupsActionLine } from '../../../../../../src/pages/EditRules/components/actions/ClearGroupsActionLine';
import { locales, scopes } from '../../../../factories';
import { ClearGroupsAction } from '../../../../../../src/models/actions';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher.ts');

const action: ClearGroupsAction = {
  type: 'clear',
  field: 'groups',
};

describe('ClearGroupsActionLine', () => {
  it('should display the clear groups action line', async () => {
    const {
      findByText,
      findAllByText,
    } = renderWithProviders(
      <ClearGroupsActionLine
        currentCatalogLocale={'en_US'}
        lineNumber={1}
        action={action}
        handleDelete={() => {}}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    );

    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.actions.clear_groups.title'
      )
    ).toBeInTheDocument();
    expect(
      await findAllByText('pimee_catalog_rule.form.helper.clear_groups')
    ).toHaveLength(2);
  });
});
