import React from 'react';
import { ClearAssociationsAction } from '../../../../../../src/models/actions';
import { renderWithProviders } from '../../../../../../test-utils';
import { ClearAssociationsActionLine } from '../../../../../../src/pages/EditRules/components/actions/ClearAssociationsActionLine';
import { locales, scopes } from '../../../../factories';
const action: ClearAssociationsAction = {
  type: 'clear',
  field: 'associations',
};

describe('ClearAssociationsActionLine', () => {
  it('should display the clear associations action line', async () => {
    const { findByText } = renderWithProviders(
      <ClearAssociationsActionLine
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
        'pimee_catalog_rule.form.edit.actions.clear_associations.title'
      )
    ).toBeInTheDocument();
    expect(
      await findByText('pimee_catalog_rule.form.helper.clear_attributes')
    ).toBeInTheDocument();
  });
});
