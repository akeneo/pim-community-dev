import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {Provider} from 'react-redux';
import {createStore} from 'redux';

const mediaFileAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};
const mediaFileData = {
  originalFilename: 'fef3232.jpg',
  filePath: 'f/e/wq/fefwf.png',
};
const Anchor = (props: any) => <button id="anchor" {...props} />;

describe('Tests fullscreen preview component', () => {
  test('It renders with a closed modal by default', () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <FullscreenPreview anchor={Anchor} data={mediaFileData} label="" attribute={mediaFileAttribute} />
      </ThemeProvider>
    );

    expect(container.querySelector('[data-role="media-data-preview"]')).not.toBeInTheDocument();
    expect(container.querySelector('[data-role="empty-preview"]')).not.toBeInTheDocument();
  });

  test('It opens the modal when clicking on the anchor', async () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <FullscreenPreview anchor={Anchor} data={mediaFileData} label="" attribute={mediaFileAttribute} />
        </Provider>
      </ThemeProvider>
    );

    fireEvent.click(container.querySelector('#anchor'));

    expect(container.querySelector('[data-role="empty-preview"]')).toBeInTheDocument();
  });

  test('It closes the modal when clicking on the close button', async () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <FullscreenPreview anchor={Anchor} data={mediaFileData} label="" attribute={mediaFileAttribute} />
        </Provider>
      </ThemeProvider>
    );

    fireEvent.click(container.querySelector('#anchor'));
    fireEvent.click(container.querySelector('[title="pim_asset_manager.close"]'));

    expect(container.querySelector('[data-role="media-data-preview"]')).not.toBeInTheDocument();
  });
});
