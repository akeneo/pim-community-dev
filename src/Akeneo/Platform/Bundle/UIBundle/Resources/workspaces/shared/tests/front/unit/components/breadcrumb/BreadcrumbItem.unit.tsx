import React from 'react';
import {act, fireEvent, render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {BreadcrumbItem} from '../../../../../src/components';

describe('Breadcrumb', () => {
  const renderWithContext = (item: any, props: {onClick?: () => void}) => {
    return render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BreadcrumbItem {...props}>{item}</BreadcrumbItem>
        </ThemeProvider>
      </DependenciesProvider>
    );
  };

  test('it shows simple item', () => {
    const item = 'DUMMY_ITEM';

    const {queryByText} = renderWithContext(item, {});
    expect(queryByText(item)).not.toBeNull();
  });

  test('it shows clickable item', () => {
    const item = 'DUMMY_ITEM';
    const clickAction = jest.fn();

    const {queryByText, getByText} = renderWithContext(item, {onClick: clickAction});
    expect(queryByText(item)).not.toBeNull();

    act(() => {
      fireEvent.click(getByText(item));
    });

    expect(clickAction).toBeCalled();
  });
});
