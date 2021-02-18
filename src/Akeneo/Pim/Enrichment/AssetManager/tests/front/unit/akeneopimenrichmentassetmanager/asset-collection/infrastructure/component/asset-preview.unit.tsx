import React from 'react';
import {fireEvent, act, screen, within} from '@testing-library/react';
import {AssetPreview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {Provider} from 'react-redux';
import {createStore} from 'redux';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

jest.mock('akeneoassetmanager/tools/security-context', () => ({isGranted: (permission: string) => true}));

const context = {locale: 'en_US', channel: 'ecommerce'};
const mediaLinkImageAttribute = {
  identifier: 'media_link_image_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.image,
};
const mediaLinkYouTubeAttribute = {
  identifier: 'media_link_youtube_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.youtube,
};
const mediaLinkOtherAttribute = {
  identifier: 'media_link_other_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.other,
};
const mediaLinkUnknownAttribute = {
  identifier: 'media_link_unknown_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: 'UNKNOWN',
};
const imageAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};
const unknownAttribute = {
  identifier: 'unknown_attribute_identifier',
  type: 'UNKNOWN',
};
const attributes = [
  mediaLinkImageAttribute,
  mediaLinkYouTubeAttribute,
  mediaLinkOtherAttribute,
  mediaLinkUnknownAttribute,
  imageAttribute,
  unknownAttribute,
];

const assetCollection = [
  {
    asset_family_identifier: 'packshot',
    code: 'Philips22PDL4906H_pack',
    image: [
      {
        attribute: 'media_link_image_attribute_identifier',
        locale: null,
        channel: null,
        data: 'nice_file_path',
      },
    ],
    identifier: 'packshot_Philips22PDL4906H_pa_e14f3b03-1929-4109-9b07-68e4f64bba74',
    labels: {en_US: 'Philips22PDL4906H_pack label'},
    completeness: {
      required: 3,
      complete: 2,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: 'media_link_image_attribute_identifier',
    },
  },
  {
    code: 'iphone8_pack',
    image: [
      {
        attribute: 'media_link_other_attribute_identifier',
        locale: null,
        channel: null,
        data: 'nice_file_path',
      },
    ],
    asset_family_identifier: 'packshot',
    identifier: 'packshot_iphone8_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
    labels: {en_US: 'iphone8_pack label'},
    completeness: {
      complete: 2,
      required: 3,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: 'media_link_other_attribute_identifier',
    },
  },
  {
    identifier: 'packshot_iphone7_pack_9c35ba44-e4f9-4a48-8250-4c554e6704a4',
    labels: {en_US: 'iphone7_pack label'},
    code: 'iphone7_pack',
    image: [
      {
        attribute: 'image_attribute_identifier',
        locale: null,
        channel: null,
        data: {filePath: 'nice_file_path', originalFilename: ''},
      },
    ],
    asset_family_identifier: 'packshot',
    completeness: {
      required: 3,
      complete: 2,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: 'image_attribute_identifier',
    },
  },
  {
    identifier: 'packshot_iphone12_pack_9c35ba44-e4f9-4a48-8250-4c554e6704a4',
    labels: {en_US: 'iphone12_pack label'},
    code: 'iphone12_pack',
    image: [
      {
        attribute: 'unknown_attribute_identifier',
        locale: null,
        channel: null,
        data: {filePath: 'nice_file_path', originalFilename: ''},
      },
    ],
    asset_family_identifier: 'packshot',
    completeness: {
      required: 3,
      complete: 2,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: 'unknown_attribute_identifier',
    },
  },
  {
    code: 'iphone6_pack',
    image: [
      {
        attribute: 'media_link_unknown_attribute_identifier',
        locale: null,
        channel: null,
        data: 'nice_file_path',
      },
    ],
    asset_family_identifier: 'packshot',
    identifier: 'packshot_iphone6_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
    labels: {en_US: 'iphone6_pack label'},
    completeness: {
      complete: 2,
      required: 3,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: 'media_link_unknown_attribute_identifier',
    },
  },
  {
    code: 'iphone13_pack',
    image: [],
    asset_family_identifier: 'packshot',
    identifier: 'packshot_iphone13_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
    labels: {en_US: 'iphone13_pack label'},
    completeness: {
      complete: 2,
      required: 3,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: '',
    },
  },
  {
    code: 'iphone14_pack',
    image: [
      {
        attribute: 'media_link_youtube_attribute_identifier',
        locale: null,
        channel: null,
        data: 'nice_file_path',
      },
    ],
    asset_family_identifier: 'packshot',
    identifier: 'packshot_iphone14_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
    labels: {en_US: 'iphone14_pack label'},
    completeness: {
      complete: 2,
      required: 3,
    },
    assetFamily: {
      attributes,
      attributeAsMainMedia: 'media_link_youtube_attribute_identifier',
    },
  },
];

const simpleAssetCollection = [
  {
    identifier: 'packshot_iphone8_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
    labels: {en_US: 'iphone8_pack label'},
    code: 'iphone8_pack',
    image: [
      {
        attribute: 'media_link_image_attribute_identifier',
        locale: null,
        channel: null,
        data: 'nice_file_path',
      },
    ],
    assetFamilyIdentifier: 'packshot',
  },
  {
    identifier: 'packshot_iphone7_pack_9c35ba44-e4f9-4a48-8250-4c554e6704a4',
    labels: {en_US: 'iphone7_pack label'},
    code: 'iphone7_pack',
    image: [
      {
        attribute: 'media_link_image_attribute_identifier',
        locale: null,
        channel: null,
        data: 'nice_file_path',
      },
    ],
    assetFamilyIdentifier: 'packshot',
  },
];
const assetFamily = {
  identifier: 'packshot',
  code: 'packshot',
  labels: {en_US: 'Packshot'},
  image: null,
  attributeAsLabel: 'name',
  attributes,
  transformations: '[]',
};

const dataProvider = (attributeAsMainMedia: string = 'media_link_image_attribute_identifier') => ({
  assetFamilyFetcher: {
    fetch: () =>
      Promise.resolve({
        assetFamily: {...assetFamily, attributeAsMainMedia},
        permission: {assetFamilyIdentifier: assetFamily.identifier, edit: true},
      }),
  },
});

const store = createStore(() => ({reloadPreview: false}));

test('It can display the previous asset in the collection', async () => {
  const initialAssetCode = 'iphone8_pack';

  await act(async () => {
    renderWithProviders(
      <Provider store={store}>
        <AssetPreview
          context={context}
          assetCollection={simpleAssetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider()}
          onClose={jest.fn()}
        />
      </Provider>
    );
  });

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_preview.previous'));

  expect(screen.getAllByRole('img')[0]).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It can display the next asset in the collection', async () => {
  const initialAssetCode = 'iphone8_pack';

  await act(async () => {
    renderWithProviders(
      <Provider store={store}>
        <AssetPreview
          context={context}
          assetCollection={simpleAssetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider()}
          onClose={jest.fn()}
        />
      </Provider>
    );
  });

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_preview.next'));

  expect(screen.getAllByRole('img')[0]).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It can select an asset from the carousel', async () => {
  const initialAssetCode = 'iphone8_pack';

  await act(async () => {
    renderWithProviders(
      <Provider store={store}>
        <AssetPreview
          context={context}
          assetCollection={simpleAssetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider()}
          onClose={jest.fn()}
        />
      </Provider>
    );
  });

  await act(async () => {
    fireEvent.click(within(screen.getByRole('listbox')).getByAltText('iphone7_pack label'));
  });

  expect(screen.getAllByRole('img')[0]).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It should not display the modal when the provided asset code is null', async () => {
  const initialAssetCode = null;

  await act(async () => {
    renderWithProviders(
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        dataProvider={dataProvider('media_link_youtube_attribute_identifier')}
        onClose={jest.fn()}
      />
    );
  });

  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('It should not display the modal when the provided asset code does not exist', async () => {
  const initialAssetCode = '404_not_found';

  await act(async () => {
    renderWithProviders(
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        dataProvider={dataProvider('media_link_youtube_attribute_identifier')}
        onClose={jest.fn()}
      />
    );
  });

  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('It should display the YouTube player when the product attribute is a YouTube media link', async () => {
  const initialAssetCode = 'iphone14_pack';

  await act(async () => {
    renderWithProviders(
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkYouTubeAttribute}
        dataProvider={dataProvider('media_link_youtube_attribute_identifier')}
        onClose={jest.fn()}
      />
    );
  });

  expect(screen.getByText('iphone14_pack label')).toBeInTheDocument();
});

test('I should get the YouTube link when I click on the Copy URL button on the preview of an asset with a YouTube media link', async () => {
  class MockClipboard {
    text: string = '';
    writeText(text: string) {
      this.text = text;
    }
    readText() {
      return this.text;
    }
  }

  const mockClipboard = new MockClipboard();
  Object.defineProperty(navigator, 'clipboard', {value: mockClipboard, writable: true});

  const initialAssetCode = 'iphone14_pack';

  await act(async () => {
    renderWithProviders(
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkYouTubeAttribute}
        dataProvider={dataProvider('media_link_youtube_attribute_identifier')}
        onClose={jest.fn()}
      />
    );
  });

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_preview.copy_url'));

  expect(mockClipboard.readText()).toEqual('https://youtube.com/watch?v=nice_file_path');
});
