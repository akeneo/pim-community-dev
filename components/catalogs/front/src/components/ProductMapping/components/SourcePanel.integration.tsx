import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {SourcePanel} from './SourcePanel';

test('it displays a placeholder if there is no target selected', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target={null}
                    targetLabel={null}
                    source={null}
                    onChange={jest.fn()}
                    errors={null}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_mapping.source.placeholder.title')).toBeInTheDocument();
});

test('it displays the target as a title', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target='erp_name'
                    targetLabel='ERP name'
                    source={null}
                    onChange={jest.fn()}
                    errors={null}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('ERP name')).toBeInTheDocument();
});
