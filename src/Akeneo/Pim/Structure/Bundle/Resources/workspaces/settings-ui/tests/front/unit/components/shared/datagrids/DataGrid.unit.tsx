import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {DataGrid} from '@akeneo-pim-community/settings-ui/src/components/shared/datagrids';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const renderDataGridWithProviders = (dataSource: any[], content: JSX.Element) => {
  return render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DataGrid dataSource={[]} handleAfterMove={() => {}} compareData={() => -1}>
          {content}
        </DataGrid>
      </ThemeProvider>
    </DependenciesProvider>
  );
};
describe('DataGrid', () => {
  test('it renders a DataGrid', () => {
    const content = (
      <tbody>
        <tr>
          <td>DUMMY_CONTENT</td>
        </tr>
      </tbody>
    );
    const {getByText} = renderDataGridWithProviders([], content);
    expect(getByText('DUMMY_CONTENT')).toBeInTheDocument();
  });
});
