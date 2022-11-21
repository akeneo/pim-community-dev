import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {SourcePanel} from './SourcePanel';
import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {ProductMapping} from '../ProductMapping';


test('it display an placeholder if there is no target selected', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target={null}
                    targetLabel={null}
                    source={null}
                    onChange={jest.fn()}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_mapping.source.placeholder.title')).toBeInTheDocument();
});

test('it display the target as a title', async () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target='erp_name'
                    targetLabel='ERP name'
                    source={null}
                    onChange={jest.fn()}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('ERP name')).toBeInTheDocument();
});
