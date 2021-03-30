import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, render} from '@testing-library/react';
import {DateTimeInput} from '@src/webhook/components/DateRangeFilter/DateTimeInput';
import {fireEvent} from '@testing-library/dom';
import {UserContext} from '@src/shared/user';
import {ThemeProvider} from 'styled-components';
import {theme} from '@src/common/styled-with-theme';

const renderDateTimeInput = (props: React.ComponentProps<typeof DateTimeInput>, {timeZone}: {timeZone: string}) => {
    const user = {
        get: jest.fn().mockReturnValue(timeZone),
        set: jest.fn(),
    };

    const wrapper: React.FC = ({children}) => (
        <UserContext.Provider value={user}>
            <ThemeProvider theme={theme}>{children}</ThemeProvider>
        </UserContext.Provider>
    );

    render(<DateTimeInput {...props} />, {wrapper});
};

test('it displays and returns the selected datetime', () => {
    const onChange = jest.fn();

    renderDateTimeInput({onChange, onError: jest.fn()}, {timeZone: 'UTC'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    expect(dateInput.value).toBe('');

    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;
    expect(timeInput.value).toBe('');

    fireEvent.change(dateInput, {target: {value: '1970-01-01'}});
    expect(dateInput.value).toBe('1970-01-01');

    fireEvent.change(timeInput, {target: {value: '12:00'}});
    expect(timeInput.value).toBe('12:00');

    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    expect(onChange).toHaveBeenCalledWith(timestamp);
});

test('for a user in the timezone "UTC", it displays the correctly zoned datetime', () => {
    const onChange = jest.fn();

    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderDateTimeInput({value: timestamp, onChange, onError: jest.fn()}, {timeZone: 'UTC'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;

    expect(dateInput.value).toBe('1970-01-01');
    expect(timeInput.value).toBe('12:00');
});

test('for a user in the timezone "Europe/Paris", it displays the correctly zoned datetime', () => {
    const onChange = jest.fn();

    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderDateTimeInput({value: timestamp, onChange, onError: jest.fn()}, {timeZone: 'Europe/Paris'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;

    expect(dateInput.value).toBe('1970-01-01');
    expect(timeInput.value).toBe('13:00'); // 12:00 UTC+1 (Daily Saving Time is off in January)
});

test('for a user in the timezone "Antarctica/McMurdo", it displays the correctly zoned datetime', () => {
    const onChange = jest.fn();

    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderDateTimeInput({value: timestamp, onChange, onError: jest.fn()}, {timeZone: 'Antarctica/McMurdo'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;

    // UTC+12 make the datetime shift to January 2 at 00:00
    expect(dateInput.value).toBe('1970-01-02');
    expect(timeInput.value).toBe('00:00');
});
