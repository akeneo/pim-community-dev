import {renderHook} from '@testing-library/react-hooks';
import {useProductAndProductModelCount} from '../../../../../src/pages/EditRules/hooks';
import {Router} from '../../../../../src/dependenciesTools';
import {httpPost} from '../../../../../src/fetch';

jest.mock('../../../../../src/fetch');

jest.mock('react-hook-form', () => ({
    useFormContext: () => ({
      watch: jest.fn()
    }),
  }
));

describe('useProductAndProductModelCount', () => {
  test('it should get the products count according to the formValues', async () => {
    // Given
    // TODO Improve type here
    const mockPost = httpPost as jest.Mock<any>;
    mockPost.mockImplementationOnce(() =>
      Promise.resolve({
        ok: true,
        json: () => ({
          impacted_product_count: '10',
          impacted_product_model_count: '20',
        }),
      })
    );
    const formValues = {
      code: 'code',
      priority: '0',
      enabled: true,
      labels: {en_US: 'hello'},
      content: {
        conditions: [{field: 'family', value: ['camcorders'], operator: 'IN'}],
        actions: [],
      },
    };
    const router: Router = {
      generate: jest.fn((route) => route),
      redirect: jest.fn(),
    };

    // When
    const {result, waitFor} = renderHook(() =>
      useProductAndProductModelCount(router, formValues)
    );

    await waitFor(() => {
      expect(mockPost).toHaveBeenNthCalledWith(1, `pimee_enrich_rule_definition_get_impacted_product_count`,
        {
          body: {"conditions": "[{\"field\":\"family\",\"value\":[\"camcorders\"],\"operator\":\"IN\"}]"},
        });

    expect(result.current).toEqual({
      status: 2,
      productCount: 10,
      productModelCount: 20,
    });
  })
  });
  test('it should return an error status', async () => {
    // Given
    // TODO Improve type here
    const mockPost = httpPost as jest.Mock<any>;
    mockPost.mockImplementationOnce(() =>
      Promise.reject({
        ok: false,
      })
    );
    const formValues = {
      code: 'code',
      priority: '0',
      enabled: true,
      labels: {en_US: 'hello'},
      content: {
        conditions: [{field: 'family', value: ['camcorders'], operator: 'IN'}],
        actions: [],
      },
    };
    const router: Router = {
      generate: jest.fn((route) => route),
      redirect: jest.fn(),
    };

    // When
    const {result, waitFor} = renderHook(() =>
      useProductAndProductModelCount(router, formValues)
    );
    // Expect
    await waitFor(() =>
      expect(mockPost).toHaveBeenNthCalledWith(
        1,
        'pimee_enrich_rule_definition_get_impacted_product_count',
        {
          body: {"conditions": "[{\"field\":\"family\",\"value\":[\"camcorders\"],\"operator\":\"IN\"}]"},
        }
      )
    );
    expect(result.current).toEqual({
      status: 1,
      productCount: -1,
      productModelCount: -1,
    });
  });
});
