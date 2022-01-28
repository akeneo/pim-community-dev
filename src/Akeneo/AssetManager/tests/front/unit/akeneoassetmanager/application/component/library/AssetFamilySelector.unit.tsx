import '@testing-library/jest-dom/extend-expect';
import {useAssetFamilyList} from 'akeneoassetmanager/application/component/library/AssetFamilySelector';
import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider} from '../../../utils/FakeConfigProvider';

describe('Test file-drop-zone component', () => {
  test('it provides an empty asset family list', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () =>
          Promise.resolve({
            items: [],
          }),
        status: 200,
      })
    );

    const handleFamilyChange = jest.fn();
    const {result, waitForNextUpdate} = renderHook(() => useAssetFamilyList('notice', handleFamilyChange), {
      wrapper: FakeConfigProvider,
    });

    expect(result.current[0]).toEqual([]);
    await waitForNextUpdate();

    expect(handleFamilyChange).toHaveBeenCalledWith(null);
    expect(result.current[0]).toEqual([]);
  });

  test('it updates the list of asset family and current assetFamily', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () =>
          Promise.resolve({
            items: [
              {
                identifier: 'packshot',
                labels: {},
                image: null,
              },
            ],
          }),
        status: 200,
      })
    );

    const handleFamilyChange = jest.fn();
    const {result, waitForNextUpdate} = renderHook(() => useAssetFamilyList('notice', handleFamilyChange), {
      wrapper: FakeConfigProvider,
    });

    expect(result.current[0]).toEqual([]);
    await waitForNextUpdate();

    expect(handleFamilyChange).toHaveBeenCalledWith('packshot');
    expect(result.current[0]).toEqual([
      {
        identifier: 'packshot',
        labels: {},
        image: null,
      },
    ]);
  });
});
