import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, render} from '@testing-library/react';
import {EventLogDateTimeRangeFilter} from '@src/webhook/components/DateRangeFilter/EventLogDateTimeRangeFilter';
import {fireEvent} from '@testing-library/dom';
import {UserContext} from '@src/shared/user';
import {ThemeProvider} from 'styled-components';
import {theme} from '@src/common/styled-with-theme';

const defaultProps = {
    value: {},
    limit: {min: 0, max: 0},
    isDirty: false,
    onChange: jest.fn(),
    onReset: jest.fn(),
};

const renderEventLogDateTimeRangeFilter = (
    props: React.ComponentProps<typeof EventLogDateTimeRangeFilter>,
    {timeZone}: {timeZone: string}
) => {
    const user = {
        get: jest.fn().mockReturnValue(timeZone),
        set: jest.fn(),
    };

    const wrapper: React.FC = ({children}) => (
        <UserContext.Provider value={user}>
            <ThemeProvider theme={theme}>{children}</ThemeProvider>
        </UserContext.Provider>
    );

    const {rerender} = render(<EventLogDateTimeRangeFilter {...props} />, {wrapper});

    return {
        rerender: (props: React.ComponentProps<typeof EventLogDateTimeRangeFilter>) =>
            rerender(<EventLogDateTimeRangeFilter {...props} />),
    };
};

test('it opens the dropdown', () => {
    renderEventLogDateTimeRangeFilter(defaultProps, {timeZone: 'UTC'});

    expect(screen.queryByText(/date_range_filter\.from/)).not.toBeInTheDocument();
    expect(screen.queryByText(/date_range_filter\.to/)).not.toBeInTheDocument();

    const dropdownButton = screen.getByRole('button');
    fireEvent.click(dropdownButton);

    expect(screen.queryByText(/date_range_filter\.from/)).toBeInTheDocument();
    expect(screen.queryByText(/date_range_filter\.to/)).toBeInTheDocument();
});

test('it displays the "from" datetime in the label', () => {
    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderEventLogDateTimeRangeFilter({...defaultProps, value: {start: timestamp}}, {timeZone: 'UTC'});

    expect(screen.queryByText(/date_range_filter\.from 1\/1\/1970, 12:00 PM/)).toBeInTheDocument();
});

test('it displays the "to" datetime in the label', () => {
    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderEventLogDateTimeRangeFilter({...defaultProps, value: {end: timestamp}}, {timeZone: 'UTC'});

    expect(screen.queryByText(/date_range_filter\.to 1\/1\/1970, 12:00 PM/)).toBeInTheDocument();
});

test('it displays both "from" and "to" datetimes in the label', () => {
    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderEventLogDateTimeRangeFilter(
        {...defaultProps, value: {start: timestamp - 3600, end: timestamp + 3600}},
        {timeZone: 'UTC'}
    );

    expect(
        screen.queryByText(/date_range_filter\.from 1\/1\/1970, 11:00 AM (.*)date_range_filter\.to 1\/1\/1970, 1:00 PM/)
    ).toBeInTheDocument();
});

test('it displays the clear button when the datetime range is modified', () => {
    const {rerender} = renderEventLogDateTimeRangeFilter(defaultProps, {timeZone: 'UTC'});

    const dropdownButton = screen.getByRole('button');
    fireEvent.click(dropdownButton);

    expect(screen.queryByTitle(/date_range_filter\.reset/)).not.toBeInTheDocument();

    rerender({...defaultProps, isDirty: true});

    expect(screen.queryByTitle(/date_range_filter\.reset/)).toBeInTheDocument();
});

test('it clears the datetime range', () => {
    const onReset = jest.fn();
    renderEventLogDateTimeRangeFilter({...defaultProps, isDirty: true, onReset}, {timeZone: 'UTC'});

    const dropdownButton = screen.getByRole('button');
    fireEvent.click(dropdownButton);

    const clearButton = screen.getByTitle(/date_range_filter\.reset/);
    fireEvent.click(clearButton);

    expect(onReset).toBeCalled();
});
