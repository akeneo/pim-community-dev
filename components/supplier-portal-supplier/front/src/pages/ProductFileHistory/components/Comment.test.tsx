import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {Comment} from './Comment';

test('it renders a supplier comment', () => {
    renderWithProviders(
        <Comment
            outgoing={true}
            authorEmail={'jimmy@punchline.com'}
            content={"Can you explain a bit more? I'm sure this is the right file."}
            createdAt={'2022-09-26T12:06:00+00:00'}
        />
    );

    expect(screen.getByText('jimmy@punchline.com')).toBeInTheDocument();
    expect(screen.getByText('"Can you explain a bit more? I\'m sure this is the right file."')).toBeInTheDocument();
    expect(screen.getByText('09/26/2022, 12:06 PM')).toBeInTheDocument();
});

test('it renders a retailer comment', () => {
    renderWithProviders(
        <Comment
            outgoing={true}
            authorEmail={'julia@roberts.com'}
            content={'Please add colors and size variations.'}
            createdAt={'2022-09-15T08:00:00+00:00'}
        />
    );

    expect(screen.getByText('julia@roberts.com')).toBeInTheDocument();
    expect(screen.getByText('"Please add colors and size variations."')).toBeInTheDocument();
    expect(screen.getByText('09/15/2022, 08:00 AM')).toBeInTheDocument();
});
