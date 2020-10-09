'use strict';

import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, fireEvent} from '@testing-library/react';
import Recipients, {MAX_RECIPIENT_COUNT} from 'akeneosharedcatalog/job/form/recipients';

test('I can see the existing recipients', () => {
  const emails = ['hello@akeneo.com', 'bonjour@akeneo.com'];
  const recipients = emails.map(email => ({
    email: email,
  }));

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={jest.fn()} />);

  expect(screen.getByText('hello@akeneo.com')).toBeInTheDocument();
  expect(screen.getByText('bonjour@akeneo.com')).toBeInTheDocument();
});

test('I can add a valid email', () => {
  const email = 'hello@akeneo.com';
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={[]} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  const input = screen.getByPlaceholderText('shared_catalog.recipients.placeholder');
  fireEvent.change(input, {target: {value: email}});
  const button = screen.getByText('pim_common.add');
  fireEvent.click(button);

  expect(mockOnRecipientsChange).toHaveBeenCalledWith([{email}]);
});

test('I cannot add an invalid email', () => {
  const email = 'INVALID';
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={[]} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  const input = screen.getByPlaceholderText('shared_catalog.recipients.placeholder');
  fireEvent.change(input, {target: {value: email}});
  const button = screen.getByText('pim_common.add');
  fireEvent.click(button);

  expect(screen.getByText('shared_catalog.recipients.invalid_email')).toBeInTheDocument();
});

test('I cannot add a duplicate email', () => {
  const email = 'michel@akeneo.com';
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={[{email}]} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  const input = screen.getByPlaceholderText('shared_catalog.recipients.placeholder') as HTMLInputElement;
  fireEvent.change(input, {target: {value: email}});
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter', keyCode: 13, charCode: 13});

  expect(screen.getByText('shared_catalog.recipients.duplicates')).toBeInTheDocument();
});

test('I can see a backend validation error', () => {
  const recipients = [
    {
      email: 'INVALID',
    },
  ];
  const validationErrors = {
    0: {
      email: 'invalid_email_message',
    },
  };

  render(<Recipients recipients={recipients} validationErrors={validationErrors} onRecipientsChange={jest.fn()} />);

  expect(screen.getByText('invalid_email_message')).toBeInTheDocument();
});

test('I can remove a recipient', () => {
  const recipients = [{email: 'hello@akeneo.com'}];
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  // We select the row first to check it is also removed from the bulk selection
  fireEvent.click(screen.getByText('hello@akeneo.com'));

  const button = screen.getByTitle('pim_common.remove');
  fireEvent.click(button);

  expect(mockOnRecipientsChange).toHaveBeenCalledWith([]);
  expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();
});

test('I can search recipients by email', () => {
  const recipients = [{email: 'hello@akeneo.com'}, {email: 'coucou@akeneo.com'}];
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  const searchInput = screen.getByTitle('pim_common.search') as HTMLInputElement;

  expect(screen.queryByText('hello@akeneo.com')).toBeInTheDocument();
  expect(screen.queryByText('coucou@akeneo.com')).toBeInTheDocument();

  fireEvent.change(searchInput, {target: {value: 'coucou'}});

  expect(screen.queryByText('hello@akeneo.com')).not.toBeInTheDocument();
  expect(screen.queryByText('coucou@akeneo.com')).toBeInTheDocument();

  fireEvent.change(searchInput, {target: {value: 'unknown'}});

  expect(screen.queryByText('hello@akeneo.com')).not.toBeInTheDocument();
  expect(screen.queryByText('coucou@akeneo.com')).not.toBeInTheDocument();
});

test('It displays a message when no result is found', () => {
  const recipients = [{email: 'hello@akeneo.com'}];
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  const searchInput = screen.getByTitle('pim_common.search') as HTMLInputElement;
  fireEvent.change(searchInput, {target: {value: 'NOT FOUND'}});

  expect(screen.getByText('shared_catalog.recipients.no_result')).toBeInTheDocument();
});

test('It displays a helper when the max limit is reached', () => {
  const recipients = [];
  for (let i = 0; i < MAX_RECIPIENT_COUNT; i++) {
    recipients.push({email: `michel${i}@akeneo.com`});
  }
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  expect(screen.getByText('shared_catalog.recipients.max_limit_reached')).toBeInTheDocument();
});

test('It can add multiple recipients at once (separated by comma, semicolon, space or new line) and filters out duplicates', () => {
  const mockOnRecipientsChange = jest.fn();

  render(
    <Recipients
      recipients={[{email: 'coucou@akeneo.com'}]}
      validationErrors={[]}
      onRecipientsChange={mockOnRecipientsChange}
    />
  );

  expect(screen.getAllByTitle('pim_common.remove').length).toEqual(1);

  const input = screen.getByPlaceholderText('shared_catalog.recipients.placeholder');
  fireEvent.change(input, {
    target: {
      value: `coucou@akeneo.com,hello@akeneo.com;INVALID bonjour@akeneo.com nice@raccoon.net
 ANOTHER_INVALID salut@michel.fr salut@michel.fr`,
    },
  });
  fireEvent.click(screen.getByText('pim_common.add'));

  expect(screen.getAllByTitle('pim_common.remove').length).toEqual(5);
  expect(mockOnRecipientsChange).toHaveBeenCalledWith([
    {email: 'coucou@akeneo.com'},
    {email: 'hello@akeneo.com'},
    {email: 'bonjour@akeneo.com'},
    {email: 'nice@raccoon.net'},
    {email: 'salut@michel.fr'},
  ]);
});

test('It does not add more than the recipient limit', () => {
  const recipients = [];
  for (let i = 0; i < MAX_RECIPIENT_COUNT - 2; i++) {
    recipients.push({email: `michel${i}@akeneo.com`});
  }

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={jest.fn()} />);

  const input = screen.getByPlaceholderText('shared_catalog.recipients.placeholder');
  fireEvent.change(input, {
    target: {
      value: `coucou@akeneo.com,hello@akeneo.com;salut@michel.fr hey@nice-domain.io`,
    },
  });
  fireEvent.click(screen.getByText('pim_common.add'));

  expect(screen.getAllByTitle('pim_common.remove').length).toBeLessThanOrEqual(MAX_RECIPIENT_COUNT);
});

test('I can bulk delete recipients', () => {
  const recipients = [{email: 'hello@akeneo.com'}, {email: 'coucou@akeneo.com'}, {email: 'bonjour@akeneo.com'}];
  const mockOnRecipientsChange = jest.fn();

  render(<Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />);

  // Clicking twice on the same row to check toggling works -> hello@akeneo.com will not be removed
  fireEvent.click(screen.getByText('hello@akeneo.com'));
  fireEvent.click(screen.getByText('hello@akeneo.com'));
  fireEvent.click(screen.getByText('coucou@akeneo.com'));
  fireEvent.click(screen.getByText('pim_common.delete'));

  expect(mockOnRecipientsChange).toHaveBeenCalledWith([{email: 'hello@akeneo.com'}, {email: 'bonjour@akeneo.com'}]);

  // Now trying to select all and delete them
  fireEvent.click(screen.getByText('bonjour@akeneo.com'));
  fireEvent.click(screen.getByTitle('pim_common.all'));
  fireEvent.click(screen.getByText('pim_common.delete'));

  expect(mockOnRecipientsChange).toHaveBeenCalledWith([]);
});
