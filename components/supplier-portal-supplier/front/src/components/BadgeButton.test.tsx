import React from 'react';
import {BadgeButton} from './BadgeButton';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {Badge} from 'akeneo-design-system';
import {renderWithProviders} from '../tests';

test('it calls onClick handler when user clicks on button', () => {
    const onClick = jest.fn();
    renderWithProviders(<BadgeButton onClick={onClick}>My badge button</BadgeButton>);

    const button = screen.getByText('My badge button');
    userEvent.click(button);

    expect(onClick).toBeCalled();
});

test('it renders a badge button with a badge', () => {
    renderWithProviders(
        <BadgeButton onClick={jest.fn()}>
            My badge button
            <Badge level="secondary" data-testid="badge-child">
                Badge
            </Badge>
        </BadgeButton>
    );

    expect(screen.getByText('My badge button')).toBeInTheDocument();
    expect(screen.getByTestId('badge-child')).toBeInTheDocument();
});

test('it does not render invalid children', () => {
    renderWithProviders(
        <BadgeButton onClick={jest.fn()}>
            My badge button
            <Badge level="secondary" data-testid="badge-child">
                Badge
            </Badge>
            <div data-testid="invalid-child">test</div>
        </BadgeButton>
    );

    expect(screen.getByText('My badge button')).toBeInTheDocument();
    expect(screen.getByTestId('badge-child')).toBeInTheDocument();
    expect(screen.queryByTestId('invalid-child')).not.toBeInTheDocument();
});
