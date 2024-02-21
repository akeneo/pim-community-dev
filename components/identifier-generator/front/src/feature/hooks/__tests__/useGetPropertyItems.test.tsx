import {renderHook} from '@testing-library/react-hooks';
import {useGetPropertyItems} from '../useGetPropertyItems';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {ServerError} from '../../errors';
const firstPage = [
  {
    id: 'system',
    text: 'System',
    children: [
      {id: 'free_text', text: 'Free text'},
      {id: 'auto_number', text: 'Auto Number'},
      {id: 'family', text: 'Family'},
    ],
  },
  {
    id: 'marketing',
    text: 'Marketing',
    children: [{id: 'brand', text: 'Brand'}],
  },
  {
    id: 'erp',
    text: 'ERP',
    children: [{id: 'supplier', text: 'Supplier'}],
  },
  {
    id: 'technical',
    text: 'Technical',
    children: [
      {id: 'maximum_print_size', text: 'Maximum print size'},
      {id: 'sensor_type', text: 'Sensor type'},
      {id: 'camera_type', text: 'Camera type'},
      {id: 'headphone_connectivity', text: 'Headphone connectivity'},
      {id: 'maximum_video_resolution', text: 'Maximum video resolution'},
    ],
  },
  {
    id: 'product',
    text: 'Product',
    children: [
      {id: 'wash_temperature', text: 'Wash temperature'},
      {id: 'color', text: 'Color'},
      {id: 'size', text: 'Size'},
      {id: 'eu_shoes_size', text: 'EU Shoes Size'},
      {id: 'eu_shoes_color', text: 'EU Shoes Color'},
      {id: 'eu_shoes_color1', text: 'EU Shoes Color'},
      {id: 'eu_shoes_color2', text: 'EU Shoes Color'},
      {id: 'eu_shoes_color3', text: 'EU Shoes Color'},
      {id: 'eu_shoes_color4', text: 'EU Shoes Color'},
      {id: 'eu_shoes_color5', text: 'EU Shoes Color'},
    ],
  },
];

const secondPage = [{id: 'code', text: 'Code', children: [{id: 'anotherSimpleSelect', text: 'Another simple select'}]}];

describe('useGetPropertyItems', () => {
  it('should retrieve data and call for next page', async () => {
    const expectFirstCall = mockResponse('akeneo_identifier_generator_get_properties', 'GET', {
      ok: true,
      json: firstPage,
    });

    const {result, waitFor} = renderHook(() => useGetPropertyItems('', true), {
      wrapper: createWrapper(),
    });

    await waitFor(() => !!result.current.data);
    expect(result.current.data).toEqual(firstPage);
    expectFirstCall();

    const expectSecondCall = mockResponse('akeneo_identifier_generator_get_properties', 'GET', {
      ok: true,
      json: secondPage,
    });
    const handleNextPage = result.current.fetchNextPage;
    handleNextPage();

    expectSecondCall();
    await waitFor(() => {
      expect(result.current.data).toEqual([...firstPage, ...secondPage]);
    });
  });

  it('should retrieve an error when endpoint returns 500', async () => {
    mockResponse('akeneo_identifier_generator_get_properties', 'GET', {
      ok: false,
    });
    const {result, waitFor} = renderHook(() => useGetPropertyItems('', true), {
      wrapper: createWrapper(),
    });

    await waitFor(() => !!result.current.error);

    expect(result.current.error).toBeDefined();
    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});
