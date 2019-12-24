import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent, act} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AssetPreview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview';
import {getAssetByCode} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';

let container;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

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
        data: {filePath: 'nice_file_path', originalFilename: ''},
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
        data: {filePath: 'nice_file_path', originalFilename: ''},
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

// test.each([
//   ['Philips22PDL4906H_pack', mediaLinkImageAttribute],
//   ['iphone8_pack', mediaLinkOtherAttribute],
//   ['iphone7_pack', imageAttribute],
//   ['iphone12_pack', unknownAttribute],
// ])(
//   'It displays the preview of the provided asset code (%s), with attribute: %j',
//   (assetCode: string, productAttribute: any) => {
//     const {container} = render(
//       <ThemeProvider theme={akeneoTheme}>
//         <AssetPreview
//           context={context}
//           assetCollection={assetCollection}
//           initialAssetCode={assetCode}
//           productAttribute={productAttribute}
//           dataProvider={dataProvider}
//           onClose={() => {}}
//         />
//       </ThemeProvider>
//     );

//     expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', `${assetCode} label`);
//   }
// );

// test('It should throw an error when the media type of the product media-link attribute is unknown ', () => {
//   const consoleError = jest.spyOn(console, 'error').mockImplementation(() => {});
//   const initialAssetCode = 'iphone6_pack';

//   const renderComponent = () =>
//     render(
//       <ThemeProvider theme={akeneoTheme}>
//         <AssetPreview
//           context={context}
//           assetCollection={assetCollection}
//           initialAssetCode={initialAssetCode}
//           productAttribute={mediaLinkUnknownAttribute}
//           dataProvider={dataProvider}
//           onClose={() => {}}
//         />
//       </ThemeProvider>
//     );

//   expect(renderComponent).toThrowError('The preview type UNKNOWN is not supported');
//   consoleError.mockRestore();
// });

// test('It can display the previous asset in the collection', () => {
//   const initialAssetCode = 'iphone8_pack';

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   fireEvent.click(container.querySelector(`[title="pim_asset_manager.asset_preview.previous"]`));

//   expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'Philips22PDL4906H_pack label');
// });

// test('It can display the previous asset in the collection using the left arrow', () => {
//   const initialAssetCode = 'iphone8_pack';

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   fireEvent.keyDown(container, {key: 'ArrowLeft', code: 37});

//   expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'Philips22PDL4906H_pack label');
// });

// test('It can display the next asset in the collection using the right arrow', () => {
//   const initialAssetCode = 'iphone8_pack';

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   fireEvent.keyDown(container, {key: 'ArrowRight', code: 39});

//   expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
// });

// test('It can display the next asset in the collection', () => {
//   const initialAssetCode = 'iphone8_pack';

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   fireEvent.click(container.querySelector(`[title="pim_asset_manager.asset_preview.next"]`));

//   expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
// });

// test('It can select an asset from the carousel', () => {
//   const initialAssetCode = 'iphone8_pack';
//   const clickedAsset = getAssetByCode(assetCollection, 'iphone7_pack');

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   fireEvent.click(container.querySelector(`[data-role="carousel-thumbnail-${clickedAsset.code}"]`));

//   expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
// });

// test('It should not display the modal when the provided asset code is null', () => {
//   const initialAssetCode = null;

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   expect(container.querySelector('[data-role="asset-preview-modal"]')).toBeNull();
// });

// test('It should not display the modal when the provided asset code does not exist', () => {
//   const initialAssetCode = '404_not_found';

//   const {container} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   expect(container.querySelector('[data-role="asset-preview-modal"]')).toBeNull();
// });

// test('It should display the default image with a message when the asset has no main image', () => {
//   const initialAssetCode = 'iphone13_pack';

//   const {getByText} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <AssetPreview
//         context={context}
//         assetCollection={assetCollection}
//         initialAssetCode={initialAssetCode}
//         productAttribute={mediaLinkImageAttribute}
//         dataProvider={dataProvider}
//         onClose={() => {}}
//       />
//     </ThemeProvider>
//   );

//   expect(getByText('pim_asset_manager.asset_preview.empty_main_media')).toBeInTheDocument();
// });

test('It should display the YouTube player when the product attribute is a YouTube media link', async () => {
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
});

test('I should get the YouTube link when I click on the Copy URL button on the preview of an asset with a YouTube media link', async () => {
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
});
