jest.unmock('./useAssetAttributes');

import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useAssetAttributes} from './useAssetAttributes';
import {AssetAttribute} from '../models/AssetAttribute';
import {Target} from '../models/Target';

test('it fetches the API response', async () => {
    const selectedTarget: Target = {
        code: 'asset_url',
        label: 'Url of the asset',
        type: 'string',
        format: 'uri',
    };
    const assetAttributes: AssetAttribute[] = [
        {
            identifier: 'main_url_newassetfamily_f80b78d7-43f6-432c-a0a1-fc99ebb4cee7',
            label: 'Main url',
            scopable: false,
            localizable: false,
        },
        {
            identifier: 'thumbnail_url_newassetfamily_25c37fc3-760d-4529-81f9-f04b3317f746',
            label: 'Thumbnail url',
            scopable: true,
            localizable: false,
        },
        {
            identifier: 'full_size_url_newassetfamily_1811879a-c1e6-4fb7-8a90-7ff69455229c',
            label: 'Full size url',
            scopable: false,
            localizable: true,
        },
    ];
    fetchMock.mockResponseOnce(JSON.stringify(assetAttributes));

    const {result, waitForNextUpdate} = renderHook(() => useAssetAttributes('newassetfamily', selectedTarget), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/asset-attributes-by-target-type-and-target-format?assetFamilyIdentifier=newassetfamily&targetType=string&targetFormat=uri',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: assetAttributes,
        error: null,
    });
});

test('it fetches the API response when target format is null', async () => {
    const selectedTarget: Target = {
        code: 'asset_label',
        label: 'Label of the asset',
        type: 'string',
        format: null,
    };
    const assetAttributes: AssetAttribute[] = [
        {
            identifier: 'label_newassetfamily_f80b78d7-43f6-432c-a0a1-fc99ebb4cee7',
            label: 'Label',
            scopable: false,
            localizable: false,
        },
    ];
    fetchMock.mockResponseOnce(JSON.stringify(assetAttributes));

    const {result, waitForNextUpdate} = renderHook(() => useAssetAttributes('newassetfamily', selectedTarget), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/asset-attributes-by-target-type-and-target-format?assetFamilyIdentifier=newassetfamily&targetType=string&targetFormat=',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: assetAttributes,
        error: null,
    });
});
