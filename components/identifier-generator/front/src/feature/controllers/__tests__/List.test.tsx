import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {List} from '../';

jest.mock('../../pages/CreateGeneratorModal');
jest.mock('../../pages/CreateGeneratorPage');
jest.mock('../../hooks/useGetIdentifierGenerators', () => ({
  useGetIdentifierGenerators: () => ({data: [], isLoading: false}),
}));

describe('List', () => {
  it('should be in the index page on loading', () => {
    render(<List />);
    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
  });

  it('should open the create dialog and close it', () => {
    render(<List />);
    fireEvent.click(screen.getByText('pim_common.create'));
    expect(screen.getByText('CreateGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Close Modal'));
    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
  });

  it('should open the create dialog then the create page', () => {
    render(<List />);
    fireEvent.click(screen.getByText('pim_common.create'));
    expect(screen.getByText('CreateGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Save Modal'));
    fireEvent.click(screen.getByText('CreateGeneratorMock'));
  });
});
