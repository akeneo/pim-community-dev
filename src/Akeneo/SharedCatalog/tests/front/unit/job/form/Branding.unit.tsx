import React from 'react';
import {fireEvent, screen, waitForElementToBeRemoved} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {Branding} from 'akeneosharedcatalog/job/form/Branding';

test('It displays validation errors if applicable', () => {
  const branding = {image: null};
  const onChange = jest.fn();
  const validationErrors = [{image: 'This is NOT right'}, {cover_image: 'and this also!'}, {color: 'This is wrong :('}];

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={validationErrors}
      onBrandingChange={onChange}
      featureFlagIsEnabled={featureFlag => featureFlag === 'new_shared_catalog_branding'}
    />
  );

  expect(screen.getByText('This is NOT right')).toBeInTheDocument();
  expect(screen.getByText('and this also!')).toBeInTheDocument();
  expect(screen.getByText('This is wrong :(')).toBeInTheDocument();
});

test('It did not display cover and color if feature flag is not activated', () => {
  const branding = {image: null};

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={[]}
      onBrandingChange={jest.fn()}
      featureFlagIsEnabled={() => false}
    />
  );

  const mediaFileInput = screen.queryByLabelText('shared_catalog.branding.cover.label');
  const colorInput = screen.queryByLabelText('shared_catalog.branding.color.label');

  expect(mediaFileInput).not.toBeInTheDocument();
  expect(colorInput).not.toBeInTheDocument();
});

test('It can update the branding image', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={[]}
      onBrandingChange={onChange}
      featureFlagIsEnabled={featureFlag => featureFlag === 'new_shared_catalog_branding'}
    />
  );

  const file = new File(['Angry Raccoon'], 'angry-raccoon.png', {type: 'image/png'});
  const expectedSrc = `data:image/png;base64,${btoa('Angry Raccoon')}`;

  const mediaFileInput = screen.getByLabelText('shared_catalog.branding.logo.label');

  fireEvent.change(mediaFileInput, {
    target: {
      files: [file],
    },
  });

  await waitForElementToBeRemoved(() => screen.getByText('shared_catalog.branding.uploading'));

  expect(onChange).toHaveBeenCalledWith({image: expectedSrc});
});

test('It can update the branding cover image', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={[]}
      onBrandingChange={onChange}
      featureFlagIsEnabled={featureFlag => featureFlag === 'new_shared_catalog_branding'}
    />
  );

  const file = new File(['Angry Raccoon'], 'angry-raccoon.png', {type: 'image/png'});
  const expectedSrc = `data:image/png;base64,${btoa('Angry Raccoon')}`;

  const mediaFileInput = screen.getByLabelText('shared_catalog.branding.cover.label');

  fireEvent.change(mediaFileInput, {
    target: {
      files: [file],
    },
  });

  await waitForElementToBeRemoved(() => screen.getByText('shared_catalog.branding.uploading'));

  expect(onChange).toHaveBeenCalledWith({image: null, cover_image: expectedSrc});
});

test('It displays a validation error when a oversized file is selected', async () => {
  jest.spyOn(global.console, 'error').mockImplementation(jest.fn());

  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={[]}
      onBrandingChange={onChange}
      featureFlagIsEnabled={featureFlag => featureFlag === 'new_shared_catalog_branding'}
    />
  );

  const oversizedFile = new File([new ArrayBuffer(20000001)], 'fat-raccoon.png', {type: 'image/png'});

  const mediaFileInput = screen.getByLabelText('shared_catalog.branding.cover.label');

  fireEvent.change(mediaFileInput, {
    target: {
      files: [oversizedFile],
    },
  });

  await waitForElementToBeRemoved(() => screen.getByText('shared_catalog.branding.uploading'));

  expect(screen.getByText('shared_catalog.branding.validation.invalid_file')).toBeInTheDocument();
});

test('It displays a validation error when a file with an invalid extension is provided', async () => {
  jest.spyOn(global.console, 'error').mockImplementation(jest.fn());

  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={[]}
      onBrandingChange={onChange}
      featureFlagIsEnabled={featureFlag => featureFlag === 'new_shared_catalog_branding'}
    />
  );

  const fileWithInvalidExtension = new File(['PDF Raccoon'], 'fat-raccoon.pdf', {type: 'image/png'});

  const mediaFileInput = screen.getByLabelText('shared_catalog.branding.cover.label');

  fireEvent.change(mediaFileInput, {
    target: {
      files: [fileWithInvalidExtension],
    },
  });

  await waitForElementToBeRemoved(() => screen.getByText('shared_catalog.branding.uploading'));

  expect(screen.getByText('shared_catalog.branding.validation.invalid_file')).toBeInTheDocument();
});

test('It can update the branding color', async () => {
  const branding = {image: null};
  const onChange = jest.fn();

  renderWithProviders(
    <Branding
      branding={branding}
      validationErrors={[]}
      onBrandingChange={onChange}
      featureFlagIsEnabled={featureFlag => featureFlag === 'new_shared_catalog_branding'}
    />
  );

  const colorInput = screen.getByLabelText('shared_catalog.branding.color.label');

  fireEvent.change(colorInput, {
    target: {
      value: '#ffffff',
    },
  });

  expect(onChange).toHaveBeenCalledWith({image: null, color: '#ffffff'});
});
