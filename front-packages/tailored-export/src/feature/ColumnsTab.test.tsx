import {render, screen} from '@testing-library/react';
import {userEvent} from '@testing-library/user-event';
import {ColumnsTab} from './ColumnsTab';

test('it renders a Column', () => {
  render(<ColumnsTab jobCode="test" />);

  expect(screen.getByText(/pim_common.edit: test! cool/i)).toBeInTheDocument();
});

test('it changes when clicking on the button', () => {
  render(<ColumnsTab jobCode="test" />);

  userEvent.click(screen.getByText(/pim_common.edit: test! cool/i));

  expect(screen.getByText(/pim_common.edit: test! nice/i)).toBeInTheDocument();
});
