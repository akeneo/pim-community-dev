import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {QualityScoreSelector} from './QualityScoreSelector';

const availableQualityScores = ['NO_CONDITION_ON_QUALITY_SCORE', 'A', 'B', 'C', 'D', 'E'];

test('it displays unselected quality score', () => {
  renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScore="NO_CONDITION_ON_QUALITY_SCORE"
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('pim_enrich.export.product.filter.quality-score.empty_selection')).toBeInTheDocument();
});

test('it displays the selected quality score', () => {
  renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScore="A"
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('A')).toBeInTheDocument();
});

test('it notifies when the quality score is changed', async () => {
  const onOperatorChange = jest.fn();

  await renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScore="A"
      onChange={onOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('B'));

  expect(onOperatorChange).toHaveBeenCalledWith('B');
});

test('it displays validations errors if any', async () => {
  const myErrorMessage = 'My message.';

  await renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScore="A"
      onChange={() => {}}
      validationErrors={[
        {
          messageTemplate: myErrorMessage,
          parameters: {},
          message: myErrorMessage,
          propertyPath: '',
          invalidValue: '',
        },
      ]}
    />
  );

  expect(screen.queryByText(myErrorMessage)).toBeInTheDocument();
});
