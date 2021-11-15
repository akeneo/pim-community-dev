import React from 'react';
import {render, fireEvent} from '@testing-library/react';
import NewOptionPlaceholder from 'akeneopimstructure/js/attribute-option/components/NewOptionPlaceholder';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme, Table} from 'akeneo-design-system';

describe('NewOptionPlaceholder', () => {
  beforeAll(() => {
    window.HTMLElement.prototype.scrollIntoView = jest.fn();
  });

  beforeEach(() => {
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.clearAllMocks();
  });

  const cancelNewOptionMockFn = jest.fn();
  const isDraggableMockFn = jest.fn();

  const givenProps = () => {
    return {
      cancelNewOption: cancelNewOptionMockFn,
      isDraggable: isDraggableMockFn,
    };
  };

  const renderNewOptionPlaceholderWithContext = () => {
    const props = givenProps();

    return render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Table>
            <Table.Body>
              <NewOptionPlaceholder {...props} />
            </Table.Body>
          </Table>
        </ThemeProvider>
      </DependenciesProvider>
    );
  };

  it('should display the placeholder and a cancel button', () => {
    const {getByTestId} = renderNewOptionPlaceholderWithContext();

    const placeholder = getByTestId(/new-option-placeholder/i);
    const button = getByTestId(/new-option-cancel/i);

    expect(placeholder).not.toBeNull();
    expect(button).not.toBeNull();
  });

  it('should scroll to the placeholder when it is mounted', () => {
    const {getByTestId} = renderNewOptionPlaceholderWithContext();

    const placeholder = getByTestId(/new-option-placeholder/i);

    expect(placeholder.scrollIntoView).toHaveBeenCalled();
  });

  it('should dispatch the cancel of the new option creation when the user click on cancel button', () => {
    const {getByTestId} = renderNewOptionPlaceholderWithContext();

    const button = getByTestId(/new-option-cancel/i);

    fireEvent.click(button);

    expect(cancelNewOptionMockFn).toHaveBeenCalled();
  });
});
