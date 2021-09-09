import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {view} from '../../../src/attribute/SelectTemplate';

const SelectTemplate = view;

describe('SelectTemplateApp', () => {
  it('should render the component', () => {
    renderWithProviders(<SelectTemplate onStepConfirm={jest.fn()} onClose={jest.fn()} />);

    expect(screen.getByText('pim_table_attribute.templates.empty_table')).toBeInTheDocument();
  });

  it('should close the component', () => {
    const handleClose = jest.fn();
    renderWithProviders(<SelectTemplate onStepConfirm={jest.fn()} onClose={handleClose} />);

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.close'));
    });

    expect(handleClose).toBeCalledTimes(1);
  });

  it('should callback the previous', () => {
    const handleBack = jest.fn();
    renderWithProviders(<SelectTemplate onStepConfirm={jest.fn()} onClose={jest.fn()} onBack={handleBack} />);

    act(() => {
      fireEvent.click(screen.getByText(/pim_common.previous/));
    });

    expect(handleBack).toBeCalled();
  })

  it('should callback the click', () => {
    const handleStepConfirm = jest.fn();
    renderWithProviders(<SelectTemplate onStepConfirm={handleStepConfirm} onClose={jest.fn()} />);

    act(() => {
      fireEvent.click(screen.getByTitle('pim_table_attribute.templates.empty_table'));
    });

    expect(handleStepConfirm).toBeCalledWith({template: 'empty_table'});
  });
});
