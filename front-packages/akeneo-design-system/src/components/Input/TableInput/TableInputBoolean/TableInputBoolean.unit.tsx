import React from 'react';
import {TableInputBoolean} from './TableInputBoolean';
import {fireEvent, render, screen} from '../../../../storybook/test-util';

test('it renders a Yes boolean input', () => {
    const handleChange = jest.fn();
    render(<TableInputBoolean value={true} onChange={handleChange} yesLabel="Yes" noLabel="No" emptyResultLabel="No results"/>);

    expect(screen.getByText('Yes')).toBeInTheDocument();
});

test('it calls callback', () => {
    const handleChange = jest.fn();
    render(<TableInputBoolean value={true} onChange={handleChange} yesLabel="Yes" noLabel="No" emptyResultLabel="No results"/>);

    const input = screen.getAllByRole('textbox')[0];
    fireEvent.focus(input);

    expect(screen.queryByText('No')).toBeInTheDocument();

    fireEvent.click(screen.getByTestId('backdrop'));
    expect(screen.queryByText('No')).not.toBeInTheDocument();

    fireEvent.focus(screen.getByRole('textbox'));
    expect(screen.queryByText('No')).toBeInTheDocument();

    const noOption = screen.getByText('No');
    expect(noOption).toBeInTheDocument();
    fireEvent.click(noOption);
    expect(handleChange).toHaveBeenCalledWith(false);
});

test('it empty the field', () => {
    const handleChange = jest.fn();
    render(<TableInputBoolean value={true} onChange={handleChange} clearLabel="Clear" yesLabel="Yes" noLabel="No" emptyResultLabel="No results"/>);

    const clearButton = screen.getByTitle('Clear');
    fireEvent.click(clearButton);

    expect(handleChange).toHaveBeenCalledWith(null);
});

test('TableInputBoolean supports ...rest props', () => {
    const handleChange = jest.fn();

    render(
        <TableInputBoolean id="myInput" value={true} onChange={handleChange} data-testid="my_value" yesLabel="Yes" noLabel="No" emptyResultLabel="No results" highlighted={true} />
    );
    expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
