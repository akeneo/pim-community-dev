import React from 'react';
import {screen} from '@testing-library/react';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {DownloadAction, CopyUrlAction} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {renderWithAssetManagerProviders} from '../../../../../../tools';
import {fireEvent} from '@testing-library/dom';

const mediaLinkAttribute = {
  code: 'mlimage',
  identifier: 'media_link_image_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: 'image',
  prefix: 'http://',
  suffix: '.png',
  labels: {},
};

const mediaLinkData = 'pim';

test('It render a download action on media link', () => {
  renderWithAssetManagerProviders(<DownloadAction data={mediaLinkData} attribute={mediaLinkAttribute} />);

  const downloadButton = screen.getByTitle('pim_asset_manager.asset_preview.download');
  expect(downloadButton).toBeInTheDocument();
  expect(downloadButton.closest('a')).toHaveAttribute('href', 'http://pim.png');
});

test('It render a copy action on media link', () => {
  Object.assign(navigator, {
    clipboard: {
      writeText: jest.fn(),
    },
  });

  renderWithAssetManagerProviders(<CopyUrlAction data={mediaLinkData} attribute={mediaLinkAttribute} />);

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_preview.copy_url'));
  expect(navigator.clipboard.writeText).toHaveBeenCalledWith('http://pim.png');
});
