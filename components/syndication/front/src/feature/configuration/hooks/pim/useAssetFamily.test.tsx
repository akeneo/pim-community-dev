import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../../tests';
import {useAssetFamily} from './useAssetFamily';

const flushPromises = () => new Promise(setImmediate);

test('It fetches an assetFamily', async () => {
  const {result} = renderHookWithProviders(() => useAssetFamily('wallpapers'));
  await act(async () => {
    await flushPromises();
  });

  const assetFamilyAfterFetch = result.current;
  expect(assetFamilyAfterFetch).toEqual({
    identifier: 'wallpapers',
    attribute_as_main_media: 'media_blablabla',
    attributes: [
      {
        identifier: 'media_blablabla',
        type: 'media_file',
        value_per_locale: false,
        value_per_channel: false,
      },
    ],
  });
});

test('It returns null if the asset family does not exists', async () => {
  const {result} = renderHookWithProviders(() => useAssetFamily('unknown'));
  await act(async () => {
    await flushPromises();
  });

  const assetFamilyAfterFetch = result.current;
  expect(assetFamilyAfterFetch).toEqual(null);
});

test('It returns asset family only if hook is mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useAssetFamily('wallpapers'));
  unmount();
});
