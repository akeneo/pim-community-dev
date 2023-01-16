import {renderHook} from '@testing-library/react-hooks';
import {usePaginatedOptions} from '../useGetSelectOptions';
import {mockResponse} from '../../tests/test-utils';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from '@testing-library/react';

describe('useGetSelectOptions', () => {
  test('it paginates select options', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page1,
    });
    const {result, waitFor} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});
    await waitFor(() => !result.current.isLoading);
    expectCall();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual(page1);

    const page2 = [...Array(10)].map((_, i) => ({code: `Option${i + 20}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page2,
    });
    act(() => {
      result.current.handleNextPage();
    });
    await waitFor(() => result.current.options && result.current.options.length > 20);
    expectCall2();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual([...page1, ...page2]);
  });

  test('it searches options', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page1,
    });
    const {result, waitFor} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});
    await waitFor(() => !result.current.isLoading);
    expectCall();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual(page1);

    const pageSearch = [...Array(3)].map((_, i) => ({code: `Option${i * 2}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: pageSearch,
    });
    act(() => {
      result.current.handleSearchChange('searchedOption');
    });
    await waitFor(() => result.current.options && result.current.options.length === 3);
    expectCall2();
    expect(result.current.options).toBeDefined();
    expect(result.current.options).toEqual(pageSearch);
  });
});
