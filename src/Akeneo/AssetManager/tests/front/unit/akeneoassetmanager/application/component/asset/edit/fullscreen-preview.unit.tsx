import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {Provider} from 'react-redux';
import {createStore} from 'redux';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const mediaFileAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};
const mediaFileData = {
  originalFilename: 'fef3232.jpg',
  filePath: 'f/e/wq/fefwf.png',
};
const Anchor = (props: any) => <button title="anchor" {...props} />;

describe('Tests fullscreen preview component', () => {
  test('It renders with a closed modal by default', () => {
    renderWithProviders(
      <FullscreenPreview anchor={Anchor} data={mediaFileData} label="nice label" attribute={mediaFileAttribute} />
    );

    expect(screen.queryByText('nice label')).not.toBeInTheDocument();
  });

  test('It opens the modal when clicking on the anchor', () => {
    renderWithProviders(
      <Provider store={createStore(() => ({reloadPreview: false}))}>
        <FullscreenPreview anchor={Anchor} data={mediaFileData} label="nice label" attribute={mediaFileAttribute} />
      </Provider>
    );

    fireEvent.click(screen.getByTitle('anchor'));

    expect(screen.getByText('nice label')).toBeInTheDocument();
  });

  test('It closes the modal when clicking on the close button', () => {
    renderWithProviders(
      <Provider store={createStore(() => ({reloadPreview: false}))}>
        <FullscreenPreview anchor={Anchor} data={mediaFileData} label="nice label" attribute={mediaFileAttribute} />
      </Provider>
    );

    fireEvent.click(screen.getByTitle('anchor'));
    fireEvent.click(screen.getByTitle('pim_common.close'));

    expect(screen.queryByText('nice label')).not.toBeInTheDocument();
  });
});
