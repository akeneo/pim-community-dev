import React, {ReactElement} from 'react';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {PageHeader} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

describe('PageHeader', () => {
  const renderWithContext = (children: ReactElement | ReactElement[] | string, showPlaceholder: boolean = false) => {
    return render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <PageHeader showPlaceholder={showPlaceholder}>{children}</PageHeader>
        </ThemeProvider>
      </DependenciesProvider>
    );
  };

  test('it does not display invalid child node', () => {
    const invalidContent = 'INVALID_CONTENT';

    const {queryByText} = renderWithContext(invalidContent);
    expect(queryByText(invalidContent)).not.toBeInTheDocument();
  });

  test('it hides title when placeholder is shown', () => {
    const title = 'DUMMY_TITLE';

    const {queryByText} = renderWithContext(<PageHeader.Title>{title}</PageHeader.Title>, true);
    expect(queryByText(title)).not.toBeInTheDocument();
  });

  test('it shows title when placeholder is hidden', () => {
    const title = 'DUMMY_TITLE';

    const {queryByText} = renderWithContext(<PageHeader.Title>{title}</PageHeader.Title>, false);
    expect(queryByText(title)).toBeInTheDocument();
  });

  test('it shows actions', () => {
    const actions = (
      <PageHeader.Actions>
        <button>DUMMY_BUTTON_1</button>
        <button>DUMMY_BUTTON_2</button>
      </PageHeader.Actions>
    );
    const {queryByText} = renderWithContext(actions);
    expect(queryByText('DUMMY_BUTTON_1')).toBeInTheDocument();
    expect(queryByText('DUMMY_BUTTON_2')).toBeInTheDocument();
  });

  test('it shows illustration', () => {
    const title = 'DUMMY_TITLE';
    const illustration = (
      <PageHeader.Illustration
        src={'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='}
        title={title}
      />
    );
    const {queryByAltText} = renderWithContext(illustration);

    expect(queryByAltText(title)).toBeInTheDocument();
  });

  test('it shows user actions', () => {
    const userActions = (
      <PageHeader.UserActions>
        <button>DUMMY_BUTTON_1</button>
      </PageHeader.UserActions>
    );
    const {queryByText} = renderWithContext(userActions);
    expect(queryByText('DUMMY_BUTTON_1')).toBeInTheDocument();
  });

  test('it shows user actions with page actions', () => {
    const content = [
      <PageHeader.Actions key={1}>
        <button>DUMMY_BUTTON_1</button>
      </PageHeader.Actions>,
      <PageHeader.UserActions key={2}>
        <button>DUMMY_BUTTON_2</button>
      </PageHeader.UserActions>,
    ];
    const {queryByText} = renderWithContext(content);
    expect(queryByText('DUMMY_BUTTON_1')).toBeInTheDocument();
    expect(queryByText('DUMMY_BUTTON_2')).toBeInTheDocument();
  });

  test('it shows breadcrumb', () => {
    const breadcrumb = <PageHeader.Breadcrumb>DUMMY_BREADCRUMB</PageHeader.Breadcrumb>;
    const {queryByText} = renderWithContext(breadcrumb);
    expect(queryByText('DUMMY_BREADCRUMB')).toBeInTheDocument();
  });

  test('it shows state', () => {
    const state = <PageHeader.State>DUMMY_BREADCRUMB</PageHeader.State>;
    const {queryByText} = renderWithContext(state);
    expect(queryByText('DUMMY_BREADCRUMB')).toBeInTheDocument();
  });

  test('it shows content', () => {
    const state = <PageHeader.Content>My header content</PageHeader.Content>;
    const {queryByText} = renderWithContext(state);
    expect(queryByText('My header content')).toBeInTheDocument();
  });
});
