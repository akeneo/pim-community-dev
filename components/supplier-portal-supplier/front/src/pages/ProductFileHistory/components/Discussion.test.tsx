import React from 'react';
import {fireEvent, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {Discussion} from './Discussion';
import {apiFetch} from '../../../api/apiFetch';
import {queryClient} from '../../../api';
import {BadRequestError} from '../../../api/BadRequestError';

jest.mock('../../../api/apiFetch');
jest.mock('../../../api');
jest.mock('../api/markCommentsAsRead');

test('it renders a form to post a new comment', () => {
    renderWithProviders(<Discussion comments={[]} productFileIdentifier={'4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86'} />);

    expect(screen.getByRole('form')).toBeInTheDocument();
    expect(screen.getByPlaceholderText('Say something about this file')).toBeInTheDocument();
    expect(screen.getByRole('button')).toBeInTheDocument();
});

test('it allows to post a new comment related to a product file', async () => {
    renderWithProviders(<Discussion comments={[]} productFileIdentifier={'4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86'} />);
    let commentInput = screen.getByPlaceholderText('Say something about this file');

    fireEvent.change(commentInput, {target: {value: 'What do you think about this product file?'}});
    fireEvent.submit(screen.getByRole('form'));

    expect(apiFetch).toHaveBeenCalled();

    await waitFor(() => {
        expect(commentInput.value).toBe('');
    });

    expect(queryClient.invalidateQueries).toHaveBeenCalled();
});

test('it renders an error message when something went wrong', async () => {
    apiFetch.mockRejectedValue(new BadRequestError('max_comments_limit_reached'));

    renderWithProviders(<Discussion comments={[]} productFileIdentifier={'4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86'} />);
    let commentInput = screen.getByPlaceholderText('Say something about this file');

    fireEvent.change(commentInput, {target: {value: 'What do you think about this product file?'}});
    fireEvent.submit(screen.getByRole('form'));

    await waitFor(() => {
        expect(screen.getByText("You've reached the comment limit.")).toBeInTheDocument();
    });
});

test('it disables the comment sending if the comment is empty', () => {
    renderWithProviders(<Discussion comments={[]} productFileIdentifier={'4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86'} />);
    let commentInput = screen.getByPlaceholderText('Say something about this file');
    fireEvent.change(commentInput, {target: {value: ''}});

    expect(screen.getByRole('button')).toBeDisabled();
});

test('it disables the comment sending if the comment is too long', () => {
    renderWithProviders(<Discussion comments={[]} productFileIdentifier={'4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86'} />);
    let commentInput = screen.getByPlaceholderText('Say something about this file');
    fireEvent.change(commentInput, {target: {value: 'a'.repeat(300)}});

    expect(screen.getByRole('button')).toBeDisabled();
});
