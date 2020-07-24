'use strict';

import React from 'react';
import ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {
  act,
  queryByText,
  fireEvent,
  getByTitle,
  getByText,
  getByPlaceholderText,
  getAllByTitle,
} from '@testing-library/react';
import Recipients, {MAX_RECIPIENT_COUNT} from 'akeneosharedcatalog/job/form/recipients';

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
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

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.placeholder');
  fireEvent.change(input, {target: {value: email}});
  const button = getByText(container, 'pim_common.add');
  fireEvent.click(button);

  expect(mockOnRecipientsChange).toHaveBeenCalledWith([{email}]);
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

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.placeholder');
  fireEvent.change(input, {target: {value: email}});
  const button = getByText(container, 'pim_common.add');
  fireEvent.click(button);

  expect(getByText(container, 'shared_catalog.recipients.invalid_email')).toBeInTheDocument();
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

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.placeholder') as HTMLInputElement;
  fireEvent.change(input, {target: {value: email}});
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter', keyCode: 13, charCode: 13});

  expect(getByText(container, 'shared_catalog.recipients.duplicates')).toBeInTheDocument();
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

test('I can search recipients by email', async () => {
  const recipients = [{email: 'hello@akeneo.com'}, {email: 'coucou@akeneo.com'}];
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  const searchInput = getByTitle(container, 'pim_common.search') as HTMLInputElement;

  expect(queryByText(container, 'hello@akeneo.com')).toBeInTheDocument();
  expect(queryByText(container, 'coucou@akeneo.com')).toBeInTheDocument();

  fireEvent.change(searchInput, {target: {value: 'coucou'}});

  expect(queryByText(container, 'hello@akeneo.com')).not.toBeInTheDocument();
  expect(queryByText(container, 'coucou@akeneo.com')).toBeInTheDocument();

  fireEvent.change(searchInput, {target: {value: 'unknown'}});

  expect(queryByText(container, 'hello@akeneo.com')).not.toBeInTheDocument();
  expect(queryByText(container, 'coucou@akeneo.com')).not.toBeInTheDocument();
});

test('It displays a message when no result is found', async () => {
  const recipients = [{email: 'hello@akeneo.com'}];
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  const searchInput = getByTitle(container, 'pim_common.search') as HTMLInputElement;
  fireEvent.change(searchInput, {target: {value: 'NOT FOUND'}});

  expect(getByText(container, 'shared_catalog.recipients.no_result')).toBeInTheDocument();
});

test('It displays a helper when the max limit is reached', async () => {
  const recipients = [];
  for (let i = 0; i < MAX_RECIPIENT_COUNT; i++) {
    recipients.push({email: `michel${i}@akeneo.com`});
  }
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={mockOnRecipientsChange} />,
      container
    );
  });

  expect(getByText(container, 'shared_catalog.recipients.max_limit_reached')).toBeInTheDocument();
});

test('It can add multiple recipients at once (separated by comma, semicolon, space or new line) and filters out duplicates', async () => {
  const mockOnRecipientsChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <Recipients
        recipients={[{email: 'coucou@akeneo.com'}]}
        validationErrors={[]}
        onRecipientsChange={mockOnRecipientsChange}
      />,
      container
    );
  });

  expect(getAllByTitle(container, 'pim_common.delete').length).toEqual(1);

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.placeholder');
  fireEvent.change(input, {
    target: {
      value: `coucou@akeneo.com,hello@akeneo.com;INVALID bonjour@akeneo.com nice@raccoon.net
 ANOTHER_INVALID salut@michel.fr salut@michel.fr`,
    },
  });
  fireEvent.click(getByText(container, 'pim_common.add'));

  expect(getAllByTitle(container, 'pim_common.delete').length).toEqual(5);
  expect(mockOnRecipientsChange).toHaveBeenCalledWith([
    {email: 'coucou@akeneo.com'},
    {email: 'hello@akeneo.com'},
    {email: 'bonjour@akeneo.com'},
    {email: 'nice@raccoon.net'},
    {email: 'salut@michel.fr'},
  ]);
});

test('It does not add more than the recipient limit', async () => {
  const recipients = [];
  for (let i = 0; i < MAX_RECIPIENT_COUNT - 2; i++) {
    recipients.push({email: `michel${i}@akeneo.com`});
  }

  await act(async () => {
    ReactDOM.render(
      <Recipients recipients={recipients} validationErrors={[]} onRecipientsChange={jest.fn()} />,
      container
    );
  });

  const input = getByPlaceholderText(container, 'shared_catalog.recipients.placeholder');
  fireEvent.change(input, {
    target: {
      value: `coucou@akeneo.com,hello@akeneo.com;salut@michel.fr hey@nice-domain.io`,
    },
  });
  fireEvent.click(getByText(container, 'pim_common.add'));

  expect(getAllByTitle(container, 'pim_common.delete').length).toBeLessThanOrEqual(MAX_RECIPIENT_COUNT);
});
