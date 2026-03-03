import userEvent from '@testing-library/user-event';
import React from 'react';
import {renderWithProviders} from '../tests/utils';
import {SecondaryActions} from './SecondaryActions';
import {Dropdown} from 'akeneo-design-system';

test('it should render secondary action dropdown', () => {
  const handleClick = jest.fn();
  const {getByTitle, getByText, queryByText} = renderWithProviders(
    <SecondaryActions>
      <Dropdown.Item onClick={handleClick}>First item</Dropdown.Item>
      <Dropdown.Item>Second item</Dropdown.Item>
    </SecondaryActions>
  );

  expect(queryByText('First item')).not.toBeInTheDocument();

  userEvent.click(getByTitle('pim_common.other_actions'));

  expect(getByText('First item')).toBeInTheDocument();

  userEvent.click(getByText('First item'));

  expect(queryByText('First item')).not.toBeInTheDocument();
  expect(handleClick).toHaveBeenCalled();
});
