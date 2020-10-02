'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, render} from '@testing-library/react';
import {getByText, getByTitle} from '@testing-library/dom';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import RowStatus from 'akeneoassetmanager/application/asset-upload/component/row-status';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';

describe('Test row-status component', () => {
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
          <RowStatus status={LineStatus.WaitingForUpload} progress={0} />
        </ThemeProvider>,
        container
      );
    });
  });

  test('It renders the WaitingForUpload label', async () => {
    const status = LineStatus.WaitingForUpload;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={0} />
        </ThemeProvider>,
        container
      );
    });

    const label = getByText(container, 'pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Valid label', async () => {
    const status = LineStatus.Valid;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={0} />
        </ThemeProvider>,
        container
      );
    });

    const label = getByText(container, 'pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Created label', async () => {
    const status = LineStatus.Created;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={0} />
        </ThemeProvider>,
        container
      );
    });

    const label = getByText(container, 'pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Invalid label', async () => {
    const status = LineStatus.Invalid;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={0} />
        </ThemeProvider>,
        container
      );
    });

    const label = getByText(container, 'pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Uploaded label', async () => {
    const status = LineStatus.Uploaded;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={0} />
        </ThemeProvider>,
        container
      );
    });

    const label = getByText(container, 'pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the UploadInProgress progress bar with the correct width', async () => {
    const status = LineStatus.UploadInProgress;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={0.4} />
        </ThemeProvider>,
        container
      );
    });

    const progressBar = getByTitle(container, 'pim_asset_manager.asset.upload.status.' + status);

    expect(progressBar).not.toBeNull();
    expect(progressBar.getAttribute('width')).toEqual('40');
  });

  test('It renders the UploadInProgress progress bar even with a progress too low', async () => {
    const status = LineStatus.UploadInProgress;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={-0.4} />
        </ThemeProvider>,
        container
      );
    });

    const progressBar = getByTitle(container, 'pim_asset_manager.asset.upload.status.' + status);

    expect(progressBar).not.toBeNull();
    expect(progressBar.getAttribute('width')).toEqual('0');
  });

  test('It renders the UploadInProgress progress bar even with a progress too high', async () => {
    const status = LineStatus.UploadInProgress;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={status} progress={1.4} />
        </ThemeProvider>,
        container
      );
    });

    const progressBar = getByTitle(container, 'pim_asset_manager.asset.upload.status.' + status);

    expect(progressBar).not.toBeNull();
    expect(progressBar.getAttribute('width')).toEqual('100');
  });

  test('It throws with an unknown status', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => {});

    expect(() => {
      render(
        <ThemeProvider theme={akeneoTheme}>
          <RowStatus status={'something_else'} progress={0} />
        </ThemeProvider>
      );
    }).toThrow();
  });
});
