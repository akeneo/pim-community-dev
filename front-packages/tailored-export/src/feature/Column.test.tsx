import {render, screen} from '@testing-library/react';
import {userEvent} from '@testing-library/user-event';
import {Column} from './Column';

test('it renders a Column', () => {
  render(<Column jobCode="test" />);

  expect(screen.getByText(/pim_common.edit: test! cool/i)).toBeInTheDocument();
});

test('it changes when clicking on the button', () => {
  render(<Column jobCode="test" />);

  userEvent.click(screen.getByText(/pim_common.edit: test! cool/i));

  expect(screen.getByText(/pim_common.edit: test! nice/i)).toBeInTheDocument();
});
