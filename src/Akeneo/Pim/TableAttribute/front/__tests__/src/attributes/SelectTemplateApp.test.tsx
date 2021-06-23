import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen, fireEvent} from '@testing-library/react';
import {SelectTemplateApp} from '../../../src/attribute/SelectTemplateApp';
import {TEMPLATES} from '../../../src/models/Template';

describe('SelectTemplateApp', () => {
  it('should render the component', () => {
    const handleClose = jest.fn();
    const handleClick = jest.fn();
    renderWithProviders(<SelectTemplateApp onClick={handleClick} onClose={handleClose} templates={TEMPLATES} />);

    expect(screen.getByText('pim_table_attribute.templates.empty_table')).toBeInTheDocument();
  });

  it('should close the component', () => {
    const handleClose = jest.fn();
    const handleClick = jest.fn();
    renderWithProviders(<SelectTemplateApp onClick={handleClick} onClose={handleClose} templates={TEMPLATES} />);

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.close'));
    });

    expect(handleClose).toBeCalledTimes(1);
  });

  it('should callback the click', () => {
    const handleClose = jest.fn();
    const handleClick = jest.fn();
    renderWithProviders(<SelectTemplateApp onClick={handleClick} onClose={handleClose} templates={TEMPLATES} />);

    act(() => {
      fireEvent.click(screen.getByTitle('pim_table_attribute.templates.empty_table'));
    });

    expect(handleClick).toBeCalledWith(TEMPLATES[0]);
  });
});
