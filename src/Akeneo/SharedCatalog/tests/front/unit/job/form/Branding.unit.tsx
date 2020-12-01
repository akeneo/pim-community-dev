import React from 'react';
import {waitFor, fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {Branding} from 'akeneosharedcatalog/job/form/Branding';

test('It renders an empty file input when the provided branding is empty', () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />);

  const placeholderImage = screen.getByAltText('pim_common.branding') as HTMLImageElement;
  const fileInput = screen.getByRole('file-input') as HTMLInputElement;

  expect(placeholderImage.src).toEqual('http://localhost/bundles/pimui/images/illustrations/Import.svg');
  expect(fileInput.value).toEqual('');
});

test('It displays validation errors if applicable', () => {
  const branding = {image: null};
  const onChange = jest.fn();
  const validationErrors = [{image: 'This is NOT right'}, {image: 'and this also!'}];

  renderWithProviders(<Branding branding={branding} validationErrors={validationErrors} onBrandingChange={onChange} />);

  expect(screen.getByText('This is NOT right')).toBeInTheDocument();
  expect(screen.getByText('and this also!')).toBeInTheDocument();
});

test('It triggers the onChange event when a file is selected', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />);

  let placeholderImage: HTMLImageElement;
  const file = new File(['Angry Raccoon'], 'angry-raccoon.png', {type: 'image/png'});
  const expectedSrc = `data:image/png;base64,${btoa('Angry Raccoon')}`;

  const fileInput = screen.getByRole('file-input') as HTMLInputElement;
  fireEvent.change(fileInput, {target: {files: [file]}});

  await waitFor(() => {
    placeholderImage = screen.getByAltText('pim_common.branding') as HTMLImageElement;
  });

  expect(placeholderImage.src).toEqual(expectedSrc);
  expect(onChange).toHaveBeenCalledWith({image: expectedSrc});
});

test('It displays a validation error when a oversized file is selected', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />);

  const oversizedFile = new File([new ArrayBuffer(20000001)], 'fat-raccoon.png', {type: 'image/png'});

  const fileInput = screen.getByRole('file-input') as HTMLInputElement;
  fireEvent.change(fileInput, {target: {files: [oversizedFile]}});

  await waitFor(() => {
    screen.getByAltText('pim_common.branding') as HTMLImageElement;
  });

  expect(screen.getByText('shared_catalog.branding.filesize_too_large')).toBeInTheDocument();
});

test('It displays a validation error when a file with an invalid extension is provided', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />);

  const oversizedFile = new File(['PDF Raccoon'], 'fat-raccoon.pdf', {type: 'image/png'});

  const fileInput = screen.getByRole('file-input') as HTMLInputElement;
  fireEvent.change(fileInput, {target: {files: [oversizedFile]}});

  await waitFor(() => {
    screen.getByAltText('pim_common.branding') as HTMLImageElement;
  });

  expect(screen.getByText('shared_catalog.branding.invalid_extension')).toBeInTheDocument();
});

test('It clears the file input when the remove button is clicked', async () => {
  const branding = {image: 'niceBase64image'};
  const onChange = jest.fn();

  renderWithProviders(<Branding branding={branding} validationErrors={[]} onBrandingChange={onChange} />);

  fireEvent.click(screen.getByTitle('pim_common.remove'));

  await waitFor(() => {
    screen.getByAltText('pim_common.branding') as HTMLImageElement;
  });

  expect(onChange).toHaveBeenCalledWith({image: null});
});
