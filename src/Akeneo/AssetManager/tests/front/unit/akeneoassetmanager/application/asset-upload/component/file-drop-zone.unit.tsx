'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent} from '@testing-library/react';
import {getByLabelText} from '@testing-library/dom';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import FileDropZone from 'akeneoassetmanager/application/asset-upload/component/file-drop-zone';

describe('Test file-drop-zone component', () => {
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('It renders without errors', async () => {
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <FileDropZone onDrop={() => {}} />
        </ThemeProvider>,
        container
      );
    });
  });

  test('It allows me to upload several files in the input field', async () => {
    let result: string[] = [];
    const onDrop = (event: React.ChangeEvent<HTMLInputElement>) => {
      if (event.target?.files) {
        result = Array.from(event.target.files).map((file: File) => file.name);
      }
    };

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <FileDropZone onDrop={onDrop} />
        </ThemeProvider>,
        container
      );
    });

    const files = [
      new File(['foo'], 'foo.png', {type: 'image/png'}),
      new File(['bar'], 'bar.png', {type: 'image/png'}),
    ];

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.drop_or_click_here');
    fireEvent.change(input, {target: {files: files}});

    expect(result).toEqual(['foo.png', 'bar.png']);
  });
});
