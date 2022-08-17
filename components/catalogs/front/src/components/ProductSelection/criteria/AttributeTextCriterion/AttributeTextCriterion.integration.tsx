jest.mock('../../hooks/useOperatorTranslator');

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';
import {Operator} from '../../models/Operator';
import {AttributeTextCriterion} from './AttributeTextCriterion';

const localeUS = {code: 'en_US', label: 'English'};
const localeFR = {code: 'fr_FR', label: 'French'};
const localeDE = {code: 'de_DE', label: 'German'};

const channelEcommerce = {code: 'ecommerce', label: 'E-commerce'};

test('it renders the completeness criteria', async () => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // useAttribute
            case '/rest/catalogs/attributes/name':
                return Promise.resolve(
                    JSON.stringify({
                        label: 'Name',
                        code: 'name',
                        type: 'pim_catalog_text',
                        scopable: true,
                        localizable: true,
                    })
                );
            // useChannel
            case '/rest/catalogs/channels/ecommerce':
                return Promise.resolve(JSON.stringify(channelEcommerce));
            // useChannelLocales
            case '/rest/catalogs/channels/ecommerce/locales':
                return Promise.resolve(JSON.stringify([localeUS, localeFR, localeDE]));
            // useInfiniteChannels
            case '/rest/catalogs/channels?page=1&limit=20':
                return Promise.resolve(JSON.stringify([channelEcommerce]));
            default:
                throw Error(req.url);
        }
    });

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <AttributeTextCriterion
                    state={{
                        field: 'name',
                        operator: Operator.CONTAINS,
                        value: 'blue',
                        locale: 'en_US',
                        scope: 'ecommerce',
                    }}
                    onChange={jest.fn()}
                    onRemove={jest.fn()}
                    errors={{}}
                />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Name')).toBeInTheDocument();
    expect(await screen.findByText(Operator.CONTAINS)).toBeInTheDocument();
    expect(await screen.findByDisplayValue('blue')).toBeInTheDocument();
    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('English')).toBeInTheDocument();
});
