import React from 'react';
import {DateInput} from './DateInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <DateInput id="myInput" value="#ff0000" onChange={handleChange} />
    </>
  );

  fireEvent.change(screen.getByLabelText('My label'), {target: {value: '#00ff00'}});

  expect(handleChange).toHaveBeenCalledWith('#00ff00');
});
