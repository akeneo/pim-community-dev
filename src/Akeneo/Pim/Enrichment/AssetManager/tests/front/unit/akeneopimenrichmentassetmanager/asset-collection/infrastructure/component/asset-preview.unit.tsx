import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AssetPreview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {mount} from 'enzyme';

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

test('It can display the previous asset in the collection', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const initialAssetCode = 'iphone8_pack';
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_image_attribute_identifier',
            },
          });
        });
      },
    },
  };

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={simpleAssetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  fireEvent.click(container.querySelector(`[title="pim_asset_manager.asset_preview.previous"]`));

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
  document.body.removeChild(container);
});

test('It can unbind keyboard listeners', async () => {
  const initialAssetCode = 'iphone8_pack';
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => ({
        then: () => {
          return {};
        },
      }),
    },
  };
  await act(async () => {
    const wrapper = mount(
      <AssetPreview
        context={context}
        assetCollection={simpleAssetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        dataProvider={dataProvider}
        onClose={() => {}}
      />
    );
    wrapper.unmount();
  });
});

test('It can display the next asset in the collection', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);

  const initialAssetCode = 'iphone8_pack';
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_image_attribute_identifier',
            },
          });
        });
      },
    },
  };

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={simpleAssetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  fireEvent.click(container.querySelector(`[title="pim_asset_manager.asset_preview.next"]`));

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
  document.body.removeChild(container);
});

test('It can select an asset from the carousel', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);

  const initialAssetCode = 'iphone8_pack';
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_image_attribute_identifier',
            },
          });
        });
      },
    },
  };

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={simpleAssetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  await act(async () => {
    fireEvent.click(container.querySelector(`[data-role="carousel-thumbnail-iphone7_pack"]`));
  });

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');

  document.body.removeChild(container);
});

test('It should not display the modal when the provided asset code is null', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const initialAssetCode = null;
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_youtube_attribute_identifier',
            },
          });
        });
      },
    },
  };

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={assetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  expect(container.querySelector('[data-role="asset-preview-modal"]')).toBeNull();
  document.body.removeChild(container);
});

test('It should not display the modal when the provided asset code does not exist', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const initialAssetCode = '404_not_found';
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_youtube_attribute_identifier',
            },
          });
        });
      },
    },
  };

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={assetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkImageAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  expect(container.querySelector('[data-role="asset-preview-modal"]')).toBeNull();
  document.body.removeChild(container);
});

test('It should display the YouTube player when the product attribute is a YouTube media link', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const initialAssetCode = 'iphone14_pack';
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_youtube_attribute_identifier',
            },
          });
        });
      },
    },
  };

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={assetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkYouTubeAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  expect(container.querySelector('[data-role="youtube-player"]')).toBeInTheDocument();
  document.body.removeChild(container);
});

test('I should get the YouTube link when I click on the Copy URL button on the preview of an asset with a YouTube media link', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const dataProvider = {
    assetFamilyFetcher: {
      fetch: () => {
        return new Promise(resolve => {
          resolve({
            assetFamily: {
              attributes,
              attributeAsMainMedia: 'media_link_youtube_attribute_identifier',
            },
          });
        });
      },
    },
  };

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
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={assetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkYouTubeAttribute}
          dataProvider={dataProvider}
          onClose={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });
  fireEvent.click(container.querySelector('a[title="pim_asset_manager.asset_preview.copy_url"]'));

  expect(mockClipboard.readText()).toEqual('https://youtube.com/watch?v=nice_file_path');
  document.body.removeChild(container);
});
