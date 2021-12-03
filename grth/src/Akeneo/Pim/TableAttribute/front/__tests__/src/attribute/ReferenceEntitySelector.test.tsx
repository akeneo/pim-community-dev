import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ReferenceEntitySelector} from '../../../src/attribute';
import {fireEvent, screen} from '@testing-library/react';

jest.mock('../../../src/fetchers/ReferenceEntityFetcher');

describe('ReferenceEntitySelector', () => {
  it('should render the component', async () => {
    const setValue = jest.fn();

    renderWithProviders(
      <ReferenceEntitySelector
        value={undefined}
        openLabel='openLabel'
        emptyResultLabel='emptyResultLabel'
        placeholder='placeholder'
        onChange={setValue}
        clearLabel='clearLabel'
      />
    );

    expect(await screen.findByTitle('openLabel')).toBeInTheDocument();

    fireEvent.click(screen.getByTitle('openLabel'));
    expect(screen.getByText('Brand')).toBeInTheDocument();
    expect(screen.getByText('[color]')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Brand'));
    expect(setValue).toBeCalledWith('brand');
  });

  it('should display value when passed as prop and clearable', async () => {
    const onChange = jest.fn();
    renderWithProviders(
      <ReferenceEntitySelector
        onChange={onChange}
        value='brand'
        openLabel='openLabel'
        emptyResultLabel='emptyResultLabel'
        placeholder='placeholder'
        clearLabel='clearLabel'
      />
    );

    expect(await screen.findByText('Brand')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('clearLabel'));
    expect(onChange).toBeCalledWith(undefined);
  });
});
