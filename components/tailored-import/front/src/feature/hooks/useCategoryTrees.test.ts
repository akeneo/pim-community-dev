import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {CategoryTree} from '../models';
import {useCategoryTrees} from './useCategoryTrees';

test('It fetches category trees', async () => {
  const response: CategoryTree[] = [
    {
      id: 1,
      code: 'shoes',
      labels: {
        en_US: 'Shoes',
      },
      has_error: false,
    },
    {
      id: 2,
      code: 'tshirt',
      labels: {
        en_US: 'T-Shirt',
      },
      has_error: false,
    },
    {
      id: 3,
      code: 'ceinturon',
      labels: {
        en_US: 'Ceinturone',
      },
      has_error: false,
    },
  ];

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useCategoryTrees([]));

  await act(async () => {
    await waitForNextUpdate();
  });

  const categoryTrees = result.current;
  expect(categoryTrees).toEqual(response);
});

test('It extract category codes from validation error in order to know it category tree has error', async () => {
  const response: CategoryTree[] = [
    {
      id: 1,
      code: 'shoes',
      labels: {
        en_US: 'Shoes',
      },
      has_error: true,
    },
    {
      id: 2,
      code: 'tshirt',
      labels: {
        en_US: 'T-Shirt',
      },
      has_error: false,
    },
    {
      id: 3,
      code: 'ceinturon',
      labels: {
        en_US: 'Ceinturone',
      },
      has_error: false,
    },
  ];

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {waitForNextUpdate} = renderHookWithProviders(() =>
    useCategoryTrees([
      {
        messageTemplate: 'error.global.an_error',
        invalidValue: '',
        message: 'this is a global error',
        parameters: {},
        propertyPath: '',
      },
      {
        messageTemplate: 'error.shoes.an_error',
        invalidValue: '',
        message: 'this is a shoes error',
        parameters: {},
        propertyPath: '[mapping][shoes]',
      },
      {
        messageTemplate: 'error.sandal.an_error',
        invalidValue: '',
        message: 'this is a sandal error',
        parameters: {},
        propertyPath: '[mapping][sandal]',
      },
    ])
  );

  await act(async () => {
    await waitForNextUpdate();
  });

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_get_category_trees_action', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({
      category_codes_with_error: ['shoes', 'sandal'],
    }),
  });
});
