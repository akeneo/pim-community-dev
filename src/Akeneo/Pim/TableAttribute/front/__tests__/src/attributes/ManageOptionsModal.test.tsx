import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {ManageOptionsModal} from '../../../src/attribute/ManageOptionsModal';
import {getTableAttribute} from '../factories/Attributes';
import {getSelectColumnDefinition} from '../factories/ColumnDefinition';
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/LocaleFetcher');

describe('ManageOptionsModal', () => {
  it('should render the component', async () => {
    const handleClose = jest.fn();
    const handleChange = jest.fn();

    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={handleChange}
        columnDefinition={getSelectColumnDefinition()}
        onClose={handleClose}
      />
    );

    expect(await screen.findByTestId('code-0')).toHaveValue('salt');
    expect(await screen.findByTestId('label-0')).toHaveValue('Salt');
    expect(await screen.findByTestId('code-1')).toHaveValue('pepper');
    expect(await screen.findByTestId('label-1')).toHaveValue('Pepper');
    expect(await screen.findByTestId('code-2')).toHaveValue('eggs');
    expect(await screen.findByTestId('label-2')).toHaveValue('');
    expect(await screen.findByTestId('code-3')).toHaveValue('');
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    const german = screen.getByLabelText('German (Germany)') as HTMLInputElement;
    expect(english).toHaveValue('Salt');
    expect(german).toHaveValue('Achtzergüntlich');
  });
});
