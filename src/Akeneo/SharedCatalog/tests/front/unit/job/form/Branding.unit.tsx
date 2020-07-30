'use strict';

import React from 'react';
import ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, wait, fireEvent, getByAltText, getByText, getByRole, getByTitle} from '@testing-library/react';
import {Branding} from 'akeneosharedcatalog/job/form/Branding';

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
});

test('It renders an empty file input when the provided branding is empty', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  await act(async () => {
    ReactDOM.render(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />, container);
  });

  const placeholderImage = getByAltText(container, 'pim_common.branding') as HTMLImageElement;
  const fileInput = getByRole(container, 'file-input') as HTMLInputElement;

  expect(placeholderImage.src).toEqual('http://localhost/bundles/pimui/images/illustrations/Import.svg');
  expect(fileInput.value).toEqual('');
});

test('It displays validation errors if applicable', async () => {
  const branding = {image: null};
  const onChange = jest.fn();
  const validationErrors = [{image: 'This is NOT right'}, {image: 'and this also!'}];

  await act(async () => {
    ReactDOM.render(
      <Branding branding={branding} validationErrors={validationErrors} onBrandingChange={onChange} />,
      container
    );
  });

  expect(getByText(container, 'This is NOT right')).toBeInTheDocument();
  expect(getByText(container, 'and this also!')).toBeInTheDocument();
});

test('It triggers the onChange event when a file is selected', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  await act(async () => {
    ReactDOM.render(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />, container);
  });

  let placeholderImage: HTMLImageElement;
  const file = new File(['Angry Raccoon'], 'angry-raccoon.png', {type: 'image/png'});
  const expectedSrc = `data:image/png;base64,${btoa('Angry Raccoon')}`;

  const fileInput = getByRole(container, 'file-input') as HTMLInputElement;
  fireEvent.change(fileInput, {target: {files: [file]}});

  await wait(() => {
    placeholderImage = getByAltText(container, 'pim_common.branding') as HTMLImageElement;
  });

  expect(placeholderImage.src).toEqual(expectedSrc);
  expect(onChange).toHaveBeenCalledWith({image: expectedSrc});
});

test('It displays a validation error when a oversized file is selected', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  await act(async () => {
    ReactDOM.render(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />, container);
  });

  const oversizedFile = new File([new ArrayBuffer(20000001)], 'fat-raccoon.png', {type: 'image/png'});

  const fileInput = getByRole(container, 'file-input') as HTMLInputElement;
  fireEvent.change(fileInput, {target: {files: [oversizedFile]}});

  await wait(() => {
    getByAltText(container, 'pim_common.branding') as HTMLImageElement;
  });

  expect(getByText(container, 'shared_catalog.branding.filesize_too_large')).toBeInTheDocument();
});

test('It displays a validation error when a file with an invalid extension is provided', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  await act(async () => {
    ReactDOM.render(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />, container);
  });

  const oversizedFile = new File(['PDF Raccoon'], 'fat-raccoon.pdf', {type: 'image/png'});

  const fileInput = getByRole(container, 'file-input') as HTMLInputElement;
  fireEvent.change(fileInput, {target: {files: [oversizedFile]}});

  await wait(() => {
    getByAltText(container, 'pim_common.branding') as HTMLImageElement;
  });

  expect(getByText(container, 'shared_catalog.branding.invalid_extension')).toBeInTheDocument();
});

test('It clears the file input when the remove button is clicked', async () => {
  const branding = {image: 'niceBase64image'};
  const onChange = jest.fn();

  await act(async () => {
    ReactDOM.render(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />, container);
  });

  const removeButton = getByTitle(container, 'pim_common.remove');

  fireEvent.click(removeButton);

  await wait(() => {
    getByAltText(container, 'pim_common.branding') as HTMLImageElement;
  });

  expect(onChange).toHaveBeenCalledWith({image: null});
});
