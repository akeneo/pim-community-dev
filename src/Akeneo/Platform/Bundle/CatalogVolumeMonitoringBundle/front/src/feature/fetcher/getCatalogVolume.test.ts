import {transformVolumesToAxis} from "./catalogVolumeWrapper";
import fetchMock from "jest-fetch-mock";
import {getCatalogVolume} from "./getCatalogVolume";
import {mockedDependencies} from "@akeneo-pim-community/shared";

jest.mock("./catalogVolumeWrapper")

const mockRouter = (relativeUrl: string) => {
    // @ts-ignore
    const mockFn = jest.mock();
    mockFn
        .spyOn(mockedDependencies.router, 'generate')
        .mockReturnValue(relativeUrl);
}

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
    }
};

beforeEach(() => {
    fetchMock.resetMocks();
    mockRouter('http://localhost:8080/catalog-volume-monitoring/volumes')
});

test('get Catalog volume with success', async () => {
    // Given
    fetchMock.mockResponseOnce(JSON.stringify(volumesResponse), {status: 200});

    // When
    await getCatalogVolume(mockedDependencies.router)

    // Then
    expect(transformVolumesToAxis).toHaveBeenCalledTimes(1);
});

test('get Catalog volume with error', async () => {
    // Given
    fetchMock.mockResponseOnce(JSON.stringify(volumesResponse), {status: 404});

    // Then
    expect(await getCatalogVolume(mockedDependencies.router)).toThrowError()
});

