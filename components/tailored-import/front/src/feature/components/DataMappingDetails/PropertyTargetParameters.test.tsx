import React from 'react';
import {screen} from '@testing-library/react';
import {PropertyTargetParameters} from './PropertyTargetParameters';
import {PropertyTarget} from '../../models';
import {renderWithProviders} from 'feature/tests';

const propertyTarget: PropertyTarget = {
  code: 'description',
  type: 'property',
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
};

test('it can render children elements', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <PropertyTargetParameters target={propertyTarget} onTargetChange={handleTargetChange}>
      <div>Hello</div>
    </PropertyTargetParameters>
  );

  expect(screen.getByText('Hello')).toBeInTheDocument();
});
