import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CreateForm} from './CreateForm';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

let mockedFamily = () => [
  {
    templateId: 'templateId',
    displayName: 'displayName',
    description: 'description',
    categories: ['category1'],
    icon: 'icon_url',
    attributes: [
      {
        attributeId: 'attribute.id',
        type: 'pim_catalog_text',
        scopable: false,
        localizable: false,
      },
    ],
  },
];

// const mockedFn = jest.fn();
jest.mock('../hooks/useCreateFamily', () => ({
  useCreateFamily: () => {
    return mockedFamily;
  },
}));

test('it renders create form & user cancel form', () => {
  const closeFn = jest.fn();
  renderWithProviders(<CreateForm onConfirm={closeFn} onCancel={closeFn} />);

  expect(screen.queryByText('pim_common.create')).toBeInTheDocument();

  expect(screen.queryByText('pim_menu.item.family')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.cancel'));

  expect(closeFn).toBeCalled();
});

test('it renders form & user creates family', async () => {
  const closeFn = jest.fn();

  renderWithProviders(<CreateForm onConfirm={closeFn} onCancel={closeFn} />);

  expect(screen.queryByText('pim_common.create')).toBeInTheDocument();

  userEvent.type(screen.queryByText('pim_common.code'), 'mon_code');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.save'));
  });

  expect(closeFn).toBeCalled();
});

test('user sends form & it returns an error', async () => {
  const closeFn = jest.fn();
  (mockedFamily = () =>
    Promise.reject({
      values: [
        {
          messageTemplate: 'error.key.a_global_error',
          invalidValue: '',
          message: 'this is a global error',
          parameters: {},
          propertyPath: '',
        },
      ],
    })),
    renderWithProviders(<CreateForm onConfirm={closeFn} onCancel={closeFn} />);

  expect(screen.queryByText('pim_common.create')).toBeInTheDocument();

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.save'));
  });

  expect(screen.queryByText('this is a global error')).toBeInTheDocument();

  expect(closeFn).not.toBeCalled();
});
