import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CommentList} from './CommentList';
import {Comment} from '../models/read/Comment';

const comments: Comment[] = [
    {
        authorEmail: 'julia@akeneo.com',
        content: 'This file is outdated, please send 2022 version instead.',
        createdAt: '2022-09-22T04:08:00+00:00',
        outgoing: true,
    },
    {
        authorEmail: 'jimmy@supplier.com',
        content: 'Can you explain a bit more? I’m sure this is the right file.',
        createdAt: '2022-09-22T10:32:00+00:00',
        outgoing: false,
    },
    {
        authorEmail: 'julia@akeneo.com',
        content: 'It does not fit our 2022 company standards.',
        createdAt: '2022-09-22T14:34:00+00:00',
        outgoing: true,
    },
];

test('it renders a list of comments', () => {
    renderWithProviders(<CommentList comments={comments} />);

    expect(screen.queryAllByText('julia@akeneo.com').length).toBe(2);
    expect(screen.queryAllByText('jimmy@supplier.com').length).toBe(1);
    expect(screen.getByText('09/22/2022, 04:08 AM')).toBeInTheDocument();
    expect(screen.getByText('09/22/2022, 10:32 AM')).toBeInTheDocument();
    expect(screen.getByText('09/22/2022, 02:34 PM')).toBeInTheDocument();
    expect(screen.getByText('"This file is outdated, please send 2022 version instead."')).toBeInTheDocument();
    expect(screen.getByText('"Can you explain a bit more? I’m sure this is the right file."')).toBeInTheDocument();
    expect(screen.getByText('"It does not fit our 2022 company standards."')).toBeInTheDocument();
});

test('it displays nothing when there is no comments', () => {
    renderWithProviders(<CommentList comments={[]} />);

    expect(
        screen.queryByText('supplier_portal.product_file_dropping.supplier_files.discussion.discussion_title')
    ).not.toBeInTheDocument();
});
