import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import * as React from 'react';
import {MemoryRouter, Router} from 'react-router';
import {act} from 'react-test-renderer';
import {Connection} from '@src/settings/components/Connection';
import {createWithProviders, renderWithProviders} from '../../../test-utils';

describe('Connection', () => {
    it('should render', () => {
        const component = createWithProviders(
            <MemoryRouter>
                <Connection code={'google-shopping'} label={'Google Shopping'} image={'a/b/c/path.jpg'} />
            </MemoryRouter>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should redirect to the edit connection page when clicked', async () => {
        const history = createMemoryHistory();
        const {getByText} = renderWithProviders(
            <Router history={history}>
                <Connection code={'google-shopping'} label={'Google Shopping'} image={'a/b/c/path.jpg'} />
            </Router>
        );

        await act(async () => {
            userEvent.click(getByText('Google Shopping'));

            return Promise.resolve();
        });

        expect(history.location.pathname).toBe('/connections/google-shopping/edit');
    });
});
