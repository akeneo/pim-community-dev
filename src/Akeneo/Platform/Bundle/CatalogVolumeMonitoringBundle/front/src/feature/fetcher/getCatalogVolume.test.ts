import {transformVolumesToAxis} from "./catalogVolumeWrapper";
import fetchMock from "jest-fetch-mock";
import {getCatalogVolume} from "./getCatalogVolume";
import {mockedDependencies} from "@akeneo-pim-community/shared";

jest.mock("./catalogVolumeWrapper")

beforeEach(() => {
    fetchMock.resetMocks();
});

test('get Catalog volume with error', async () => {

    // Given
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
    // @ts-ignore
    const mockFn = jest.mock();
    mockFn
        .spyOn(mockedDependencies.router, 'generate')
        .mockReturnValue('http://localhost:8080/catalog-volume-monitoring/volumes');

    fetchMock.mockResponseOnce(JSON.stringify(volumesResponse), {status: 404});

    // When
    await getCatalogVolume(mockedDependencies.router)

    // Then
    expect(transformVolumesToAxis).not.toBeCalled()
});

