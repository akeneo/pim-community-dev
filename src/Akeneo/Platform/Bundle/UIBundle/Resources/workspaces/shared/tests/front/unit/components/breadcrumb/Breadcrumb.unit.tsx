import React from 'react';
import {render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Breadcrumb, BreadcrumbItem} from '../../../../../src/components';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

describe('Breadcrumb', () => {
  const renderWithContext = (items: any[]) => {
    const props = {};

    return render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Breadcrumb {...props}>
            {items.map((item, index) => (
              <BreadcrumbItem key={index}>{item}</BreadcrumbItem>
            ))}
          </Breadcrumb>
        </ThemeProvider>
      </DependenciesProvider>
    );
  };

  test('it shows breadcrumb items', () => {
    const item = 'DUMMY_TITLE_1';
    const item2 = 'DUMMY_TITLE_2';

    const {queryByText} = renderWithContext([item, item2]);
    expect(queryByText(item)).not.toBeNull();
    expect(queryByText(item2)).not.toBeNull();
  });
});
