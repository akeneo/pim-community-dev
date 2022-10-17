import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {List} from '../List';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
}));
jest.mock('../../components/CreateGeneratorModal');
jest.mock('../../components/CreateGenerator');

describe('List', () => {
  it('should be in the index page on loading', () => {
    render(<List/>);
    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
  });

  it('should open the create dialog and close it', () => {
    render(<List/>);
    fireEvent.click(screen.getByText('pim_common.create'));
    expect(screen.getByText('CreateGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Close Modal'));
    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
  });

  it('should open the create dialog then the create page', () => {
    render(<List/>);
    fireEvent.click(screen.getByText('pim_common.create'));
    expect(screen.getByText('CreateGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Save Modal'));
    fireEvent.click(screen.getByText('CreateGeneratorMock'));
  });
});
