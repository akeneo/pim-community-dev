import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Comment} from './Comment';

test('it renders a comment', () => {
    renderWithProviders(
        <Comment
            outgoing={false}
            authorEmail={'julia@roberts.com'}
            content={'Please add colors and size variations.'}
            createdAt={'2022-09-15T08:00:00+00:00'}
            isUnread={false}
        />
    );

    expect(screen.getByText('julia@roberts.com')).toBeInTheDocument();
    expect(screen.getByText('"Please add colors and size variations."')).toBeInTheDocument();
    expect(screen.getByText('09/15/2022, 08:00 AM')).toBeInTheDocument();
    expect(screen.queryByTestId('unreadIcon')).not.toBeInTheDocument();
});

test('it renders an unread comment', () => {
    renderWithProviders(
        <Comment
            outgoing={false}
            authorEmail={'julia@roberts.com'}
            content={'Please add colors and size variations.'}
            createdAt={'2022-09-15T08:00:00+00:00'}
            isUnread={true}
        />
    );

    expect(screen.getByText('julia@roberts.com')).toBeInTheDocument();
    expect(screen.getByText('"Please add colors and size variations."')).toBeInTheDocument();
    expect(screen.getByText('09/15/2022, 08:00 AM')).toBeInTheDocument();
    expect(screen.getByTestId('unreadIcon')).toBeInTheDocument();
});
