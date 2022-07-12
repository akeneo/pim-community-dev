import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ErrorBoundary} from './ErrorBoundary';

test('it renders the children by default', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <ErrorBoundary>
                <div>Foo</div>
            </ErrorBoundary>
        </ThemeProvider>
    );

    expect(screen.getByText('Foo')).toBeInTheDocument();
});

test('it renders a fallback message when an error is thrown', () => {
    const FailingComponent = () => {
        throw Error();
    };

    // mute the error in the output
    jest.spyOn(console, 'error');
    /* eslint-disable-next-line no-console */
    (console.error as jest.Mock).mockImplementation(() => null);

    render(
        <ThemeProvider theme={pimTheme}>
            <ErrorBoundary>
                <FailingComponent />
            </ErrorBoundary>
        </ThemeProvider>
    );

    expect(screen.getByText('Something went wrong.')).toBeInTheDocument();
});
