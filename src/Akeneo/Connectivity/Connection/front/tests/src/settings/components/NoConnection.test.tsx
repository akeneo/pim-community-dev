import {NoConnection} from '@src/settings/components/NoConnection';
import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import * as React from 'react';
import {renderWithProviders} from '../../../test-utils';

describe('NoConnection', () => {
    it('should call `onCreate` when the create connection shortcut is clicked', async () => {
        const handleCreate = jest.fn();

        const {getByText} = renderWithProviders(<NoConnection onCreate={handleCreate} />);

        await act(async () => {
            userEvent.click(getByText('akeneo_connectivity.connection.no_connection.message_link'));

            return Promise.resolve();
        });

        expect(handleCreate).toBeCalled();
    });
});
