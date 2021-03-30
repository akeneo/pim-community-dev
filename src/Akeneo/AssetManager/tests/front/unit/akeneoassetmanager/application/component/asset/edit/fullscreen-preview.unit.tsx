import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {renderWithAssetManagerProviders} from '../../../../tools';

const mediaFileAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};
const mediaFileData = {
  originalFilename: 'fef3232.jpg',
  filePath: 'f/e/wq/fefwf.png',
};

test('It renders a fullscreen preview with its actions', () => {
  const onClose = jest.fn();

  renderWithAssetManagerProviders(
    <FullscreenPreview onClose={onClose} data={mediaFileData} label="nice label" attribute={mediaFileAttribute} />
  );

  expect(screen.getByText(/nice label/i)).toBeInTheDocument();
  expect(screen.getByAltText(/nice label/i)).toBeInTheDocument();
  expect(screen.getByText(/pim_asset_manager.asset_preview.download/i)).toBeInTheDocument();

  userEvent.click(screen.getByTitle(/pim_common.close/i));

  expect(onClose).toHaveBeenCalled();
});
