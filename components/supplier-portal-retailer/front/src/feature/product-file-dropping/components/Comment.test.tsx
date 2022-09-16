import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Comment} from './Comment';

test('it renders a comment', () => {
    renderWithProviders(
        <Comment
            isRetailer={true}
            contributorEmail={'julia@roberts.com'}
            content={'Please add colors and size variations.'}
            createdAt={'2022-09-15T08:00:00+00:00'}
        />
    );

    expect(screen.getByText('julia@roberts.com')).toBeInTheDocument();
    expect(screen.getByText('"Please add colors and size variations."')).toBeInTheDocument();
    expect(screen.getByText('09/15/2022, 08:00 AM')).toBeInTheDocument();
});
