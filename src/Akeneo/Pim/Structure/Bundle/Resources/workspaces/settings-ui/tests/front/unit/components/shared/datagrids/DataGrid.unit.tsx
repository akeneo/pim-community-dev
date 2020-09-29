import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {DataGrid} from "@akeneo-pim-community/settings-ui/src/components/shared/datagrids";
import {AkeneoThemeProvider} from "@akeneo-pim-community/shared/src";
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";

const renderDataGridWithProviders = (dataSource: any[], content: JSX.Element) => {
  return render(
    <DependenciesProvider>
      <AkeneoThemeProvider>
        <DataGrid dataSource={[]} handleAfterMove={() => {}} compareData={() => -1}>
          {content}
        </DataGrid>
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
}
describe('DataGrid', () => {
  test('it renders a DataGrid', () => {
    const content = <tbody><tr><td>DUMMY_CONTENT</td></tr></tbody>
    const {getByText} = renderDataGridWithProviders([], content);
    expect(getByText('DUMMY_CONTENT')).toBeInTheDocument();
  })
});
