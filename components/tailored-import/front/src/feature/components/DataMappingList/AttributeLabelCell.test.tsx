import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from 'feature/tests';
import {AttributeLabelCell} from './AttributeLabelCell';

test('it displays the provided attribute label using the catalog locale in a cell', async () => {
  await renderWithProviders(
    <table>
      <tbody>
        <tr>
          <AttributeLabelCell attributeCode="description" />
        </tr>
      </tbody>
    </table>
  );

  expect(screen.getAllByRole('cell')).toHaveLength(1);
  expect(screen.getByText('English description')).toBeInTheDocument();
});

test('it fallbacks on the attribute code in a skeleton when loading', async () => {
  // No await here so the `useAttribute` promise never resolves
  renderWithProviders(
    <table>
      <tbody>
        <tr>
          <AttributeLabelCell attributeCode="description" />
        </tr>
      </tbody>
    </table>
  );

  expect(screen.getByText('[description]')).toBeInTheDocument();
});

test('it fallbacks on the attribute code when the attribute is not found', async () => {
  await renderWithProviders(
    <table>
      <tbody>
        <tr>
          <AttributeLabelCell attributeCode="UNKNOWN" />
        </tr>
      </tbody>
    </table>
  );

  expect(screen.getByText('[UNKNOWN]')).toBeInTheDocument();
});
