import {transformVolumesToAxis} from './catalogVolumeWrapper';
import {getCatalogVolume} from './getCatalogVolume';
import {mockedDependencies} from '@akeneo-pim-community/shared';

jest.mock('./catalogVolumeWrapper');

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const mockRouter = (route: string) => {
  // @ts-ignore
  const mockFn = jest.mock();
  mockFn.spyOn(mockedDependencies.router, 'generate').mockReturnValue(route);
};

const volumesResponse = {
  count_products: {
    value: 1389,
    has_warning: false,
    type: 'count',
  },
  average_max_attributes_per_family: {
    value: {
      average: 4,
      max: 43,
    },
    has_warning: false,
    type: 'average_max',
  },
};

beforeEach(() => {
  mockRouter('pim_volume_monitoring_get_volumes');
});

test('get Catalog volume with success', async () => {
  // Given
  global.fetch = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () => Promise.resolve(volumesResponse),
    })
  );

  // When
  await getCatalogVolume(mockedDependencies.router);

  // Then
  expect(transformVolumesToAxis).toHaveBeenCalledTimes(1);
});

test('get Catalog volume with error', async () => {
  // Given
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: false,
    statusText: 'my error',
  }));

  // Then
  await expect(getCatalogVolume(mockedDependencies.router)).rejects.toThrowError('my error');
  expect(transformVolumesToAxis).not.toBeCalled();
});
