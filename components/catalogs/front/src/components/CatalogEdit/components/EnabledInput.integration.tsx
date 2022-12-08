import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {EnabledInput} from './EnabledInput';

test('it shows an error', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <EnabledInput value={true} error={'Invalid.'} />
        </ThemeProvider>
    );

    expect(await screen.findByText('Invalid.')).toBeInTheDocument();
});
