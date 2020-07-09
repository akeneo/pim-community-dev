'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByTitle, getByText, getByPlaceholderText} from '@testing-library/react';
import Recipients from 'akeneosharedcatalog/job/form/recipients';

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It renders without errors', async () => {
  await act(async () => {
    ReactDOM.render(<Recipients recipients={[]} validationErrors={[]} onRecipientsChange={jest.fn()} />, container);
  });
});

test('I can see the existing recipients', async () => {
  const emails = ['hello@akeneo.com', 'bonjour@akeneo.com'];
  const recipients = emails.map(email => ({
    email: email,
  }));

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={jest.fn()} />,
      container
    );
  });

  expect(getByText(container, 'hello@akeneo.com')).toBeInTheDocument();
  expect(getByText(container, 'bonjour@akeneo.com')).toBeInTheDocument();
});

test('I can add a valid email', async () => {
  const email = 'hello@akeneo.com';
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={[]} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.add_recipient');
  fireEvent.change(input, {target: {value: email}});
  const button = getByText(container, 'pim_common.add');
  fireEvent.click(button);

  // const rows = getByTitle(container, 'recipients');
  // expect(getByText(rows, email)).toBeInTheDocument();
  expect(mockOnRecipientsChange).toHaveBeenCalledWith([
    {
      email: email,
    },
  ]);
});

test('I cannot add an invalid email', async () => {
  const email = 'INVALID';
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={[]} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.add_recipient');
  fireEvent.change(input, {target: {value: email}});
  const button = getByText(container, 'pim_common.add');
  fireEvent.click(button);

  expect(getByText(container, 'shared_catalog.recipients.invalid_email')).toBeInTheDocument();
  expect(mockOnRecipientsChange).not.toHaveBeenCalled();
});

test('I cannot add a duplicate email', async () => {
  const email = 'michel@akeneo.com';
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={[{email}]} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.add_recipient') as HTMLInputElement;
  fireEvent.change(input, {target: {value: email}});
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter', keyCode: 13, charCode: 13});

  expect(getByText(container, 'shared_catalog.recipients.duplicates')).toBeInTheDocument();
  expect(mockOnRecipientsChange).not.toHaveBeenCalled();
});

test('I can see a backend validation error', async () => {
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

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={validationErrors} onRecipientsChange={jest.fn()} />,
      container
    );
  });

  expect(getByText(container, 'invalid_email_message')).toBeInTheDocument();
});

test('I can remove a recipient', async () => {
  const recipients = [
    {
      email: 'hello@akeneo.com',
    },
  ];
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  const button = getByTitle(container, 'pim_common.delete');
  fireEvent.click(button);

  expect(mockOnRecipientsChange).toHaveBeenCalledWith([]);
});
