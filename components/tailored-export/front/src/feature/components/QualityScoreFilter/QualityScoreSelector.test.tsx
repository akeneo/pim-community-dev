import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {QualityScoreSelector} from './QualityScoreSelector';

const availableQualityScores = ['NO_CONDITION_ON_QUALITY_SCORE', 'A', 'B', 'C', 'D', 'E'];

test('it displays the selected quality score', () => {
  renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScores={['A']}
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('A')).toBeInTheDocument();
});

test('it notifies when the quality score is changed', () => {
  const onOperatorChange = jest.fn();

  renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScores={['A']}
      onChange={onOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('B'));

  expect(onOperatorChange).toHaveBeenCalledWith(['A', 'B']);
});

test('it displays validations errors if any', () => {
  const myErrorMessage = 'My message.';

  renderWithProviders(
    <QualityScoreSelector
      availableQualityScores={availableQualityScores}
      qualityScores={['A']}
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
