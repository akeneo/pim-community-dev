import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {QualityScoreConfigurator} from './QualityScoreConfigurator';
import {getDefaultQualityScoreSource} from '../../../components/SourceDetails/QualityScore/model';
import {getDefaultGroupsSource} from '../Groups/model';
import {renderWithProviders} from '../../../tests';
import {ValidationError} from '@akeneo-pim-community/shared';

test('it can select a locale', async () => {
  const onSourceChange = jest.fn();
  const source = getDefaultQualityScoreSource('ecommerce', 'en_US');

  await renderWithProviders(
    <QualityScoreConfigurator source={source} validationErrors={[]} onSourceChange={onSourceChange} />
  );

  userEvent.click(screen.getByText('pim_common.locale'));
  userEvent.click(screen.getByText('Français'));

  expect(onSourceChange).toHaveBeenCalledWith({...source, locale: 'fr_FR'});
});

test('it can select a channel', async () => {
  const onSourceChange = jest.fn();
  const source = getDefaultQualityScoreSource('ecommerce', 'en_US');

  await renderWithProviders(
    <QualityScoreConfigurator source={source} validationErrors={[]} onSourceChange={onSourceChange} />
  );

  userEvent.click(screen.getByText('pim_common.channel'));
  userEvent.click(screen.getByText('[mobile]'));

  expect(onSourceChange).toHaveBeenCalledWith({...source, channel: 'mobile'});
});

test('it displays validation errors', async () => {
  const onSourceChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.channel',
      invalidValue: '',
      message: 'this is a channel error',
      parameters: {},
      propertyPath: '[channel]',
    },
    {
      messageTemplate: 'error.key.locale',
      invalidValue: '',
      message: 'this is a locale error',
      parameters: {},
      propertyPath: '[locale]',
    },
  ];

  await renderWithProviders(
    <QualityScoreConfigurator
      source={getDefaultQualityScoreSource('ecommerce', 'en_US')}
      validationErrors={validationErrors}
      onSourceChange={onSourceChange}
    />
  );

  expect(screen.getByText('error.key.channel')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
});

test('it tells when the source data is invalid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <QualityScoreConfigurator source={getDefaultGroupsSource()} validationErrors={[]} onSourceChange={jest.fn()} />
    );
  }).rejects.toThrow('Invalid source data "groups" for Quality Score configurator');

  mockedConsole.mockRestore();
});
