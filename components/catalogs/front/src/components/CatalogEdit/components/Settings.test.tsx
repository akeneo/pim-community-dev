jest.unmock('./Settings');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Settings} from './Settings';

test('it renders without error', () => {
    const settings = {
        enabled: false,
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <Settings settings={settings} errors={[]} />
        </ThemeProvider>
    );

    expect(screen.getByText('[EnabledInput]')).toBeInTheDocument();
});
