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

test('it selects the datetime and returns the timestamp', () => {
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

test('it returns the timestamp when only the date is selected (as the time will be set automatically)', () => {
    const onChange = jest.fn();

    renderDateTimeInput({onChange, onError: jest.fn()}, {timeZone: 'UTC'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    fireEvent.change(dateInput, {target: {value: '1970-01-01'}});
    expect(dateInput.value).toBe('1970-01-01');

    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;
    expect(timeInput.value).toBe('');

    expect(onChange).toHaveBeenCalledTimes(1);
});

test('it doesnt return the timestamp when only the time is selected', () => {
    const onChange = jest.fn();

    renderDateTimeInput({onChange, onError: jest.fn()}, {timeZone: 'UTC'});

    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;
    fireEvent.change(timeInput, {target: {value: '12:00'}});
    expect(timeInput.value).toBe('12:00');

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    expect(dateInput.value).toBe('');

    expect(onChange).toHaveBeenCalledTimes(0);
});

test('it raises an error if the datetime is invalid (1970-99-99 at 99:99)', () => {
    // Can't be tested as chrome support the date & time inputs and doesn't allow to set invalid values.
});

test('for a user in the timezone "UTC", it displays the zoned datetime', () => {
    const onChange = jest.fn();

    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderDateTimeInput({value: timestamp, onChange, onError: jest.fn()}, {timeZone: 'UTC'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;

    expect(dateInput.value).toBe('1970-01-01');
    expect(timeInput.value).toBe('12:00');
});

test('for a user in the timezone "Europe/Paris", it displays the zoned datetime', () => {
    const onChange = jest.fn();

    const timestamp = 12 * 60 * 60; // 1970-01-01 at 12:00
    renderDateTimeInput({value: timestamp, onChange, onError: jest.fn()}, {timeZone: 'Europe/Paris'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;

    expect(dateInput.value).toBe('1970-01-01');
    expect(timeInput.value).toBe('13:00'); // 12:00 UTC+1 (Daily Saving Time is off in January)
});

test('for a user in the timezone "Asia/Tokyo", it displays the zoned datetime', () => {
    const onChange = jest.fn();

    const timestamp = 20 * 60 * 60; // 1970-01-01 at 20:00
    renderDateTimeInput({value: timestamp, onChange, onError: jest.fn()}, {timeZone: 'Asia/Tokyo'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;

    // UTC+9 make the datetime shift to January 2 at 05:00
    expect(dateInput.value).toBe('1970-01-02');
    expect(timeInput.value).toBe('05:00');
});

test('for a user in the timezone "Asia/Tokyo", it selects the zoned datetime and returns the timestamp', () => {
    const onChange = jest.fn();

    renderDateTimeInput({onChange, onError: jest.fn()}, {timeZone: 'Asia/Tokyo'});

    const dateInput = screen.getByLabelText('Date') as HTMLInputElement;
    fireEvent.change(dateInput, {target: {value: '1970-01-02'}});
    expect(dateInput.value).toBe('1970-01-02');

    const timeInput = screen.getByLabelText('Time') as HTMLInputElement;
    fireEvent.change(timeInput, {target: {value: '05:00'}});
    expect(timeInput.value).toBe('05:00');

    const timestamp = 20 * 60 * 60; // 1970-01-01 at 20:00
    expect(onChange).toHaveBeenCalledWith(timestamp);
});
