import {Connection} from '@src/settings/components/Connection';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import * as React from 'react';
import {Router} from 'react-router';
import {act} from 'react-test-renderer';
import {renderWithProviders} from '../../../test-utils';

describe('Connection', () => {
    it('should redirect to the edit connection page when clicked', async () => {
        const history = createMemoryHistory();
        const {getByText} = renderWithProviders(
            <Router history={history}>
                <Connection
                    code={'google-shopping'}
                    label={'Google Shopping'}
                    image={'a/b/c/path.jpg'}
                    hasWrongCombination={false}
                />
            </Router>
        );

        await act(async () => {
            userEvent.click(getByText('Google Shopping'));

            return Promise.resolve();
        });

        expect(history.location.pathname).toBe('/connect/connection-settings/google-shopping/edit');
    });
});
