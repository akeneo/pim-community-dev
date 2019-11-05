import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AssetPreview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview';
import {getAssetByCode} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {IMAGE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/image';

const context = {locale: 'en_US', channel: 'ecommerce'};
const mediaLinkImageAttribute = {
  identifier: 'media_link_image_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.image,
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
  type: IMAGE_ATTRIBUTE_TYPE,
};
const unknownAttribute = {
  identifier: 'unknown_attribute_identifier',
  type: 'UNKNOWN',
};
const attributes = [
  mediaLinkImageAttribute,
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
      attributeAsImage: 'media_link_image_attribute_identifier',
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
      attributeAsImage: 'media_link_other_attribute_identifier',
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
      attributeAsImage: 'image_attribute_identifier',
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
      attributeAsImage: 'unknown_attribute_identifier',
    },
  },
  {
    code: 'iphone6_pack',
    image: [
      {
        attribute: 'media_link_unknown_attribute_identifier',
        locale: null,
        channel: null,
        data: {filePath: 'nice_file_path', originalFilename: ''},
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
      attributeAsImage: 'media_link_unknown_attribute_identifier',
    },
  },
];

test('It displays the media-link-image preview of the provided asset code', () => {
  const initialAssetCode = 'Philips22PDL4906H_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'Philips22PDL4906H_pack label');
});

test('It displays the media-link-other preview of the provided asset code', () => {
  const initialAssetCode = 'iphone8_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkOtherAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone8_pack label');
});

test('It should throw an error when the media type of the product media-link attribute is unknown ', () => {
  const initialAssetCode = 'iphone6_pack';

  const renderComponent = () =>
    render(
      <ThemeProvider theme={akeneoTheme}>
        <AssetPreview
          context={context}
          assetCollection={assetCollection}
          initialAssetCode={initialAssetCode}
          productAttribute={mediaLinkUnknownAttribute}
          onClose={() => {}}
        />
      </ThemeProvider>
    );

  expect(renderComponent).toThrowError('The preview type UNKNOWN is not supported');
});

test('It displays the image preview of the provided asset code', () => {
  const initialAssetCode = 'iphone7_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={imageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It displays the default preview of the provided asset code when the product attribute is of unknown type', () => {
  const initialAssetCode = 'iphone12_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={unknownAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone12_pack label');
});

test('It can display the previous asset in the collection', () => {
  const initialAssetCode = 'iphone8_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  fireEvent.click(container.querySelector(`[title="pim_asset_manager.asset_preview.previous"]`));

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'Philips22PDL4906H_pack label');
});

test('It can display the previous asset in the collection using the left arrow', () => {
  const initialAssetCode = 'iphone8_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  fireEvent.keyDown(container, {key: 'ArrowLeft', code: 37});

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'Philips22PDL4906H_pack label');
});

test('It can display the next asset in the collection using the right arrow', () => {
  const initialAssetCode = 'iphone8_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  fireEvent.keyDown(container, {key: 'ArrowRight', code: 39});

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It can display the next asset in the collection', () => {
  const initialAssetCode = 'iphone8_pack';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  fireEvent.click(container.querySelector(`[title="pim_asset_manager.asset_preview.next"]`));

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It can select an asset from the carousel', () => {
  const initialAssetCode = 'iphone8_pack';
  const clickedAsset = getAssetByCode(assetCollection, 'iphone7_pack');

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  fireEvent.click(container.querySelector(`[data-role="carousel-thumbnail-${clickedAsset.code}"]`));

  expect(container.querySelector('[data-role="asset-preview"]')).toHaveAttribute('alt', 'iphone7_pack label');
});

test('It should not display the modal when the provided asset code is null', () => {
  const initialAssetCode = null;

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  expect(container.querySelector('[data-role="asset-preview-modal"]')).toBeNull();
});

test('It should not display the modal when the provided asset code does not exist', () => {
  const initialAssetCode = '404_not_found';

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPreview
        context={context}
        assetCollection={assetCollection}
        initialAssetCode={initialAssetCode}
        productAttribute={mediaLinkImageAttribute}
        onClose={() => {}}
      />
    </ThemeProvider>
  );

  expect(container.querySelector('[data-role="asset-preview-modal"]')).toBeNull();
});
