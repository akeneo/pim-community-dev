import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {OperatorSelector} from "./OperatorSelector";
import React from "react";
import userEvent from "@testing-library/user-event";

test('it displays the selected operator', () =>{
    renderWithProviders(<OperatorSelector operator={'ALL'} onChange={() => {}}/>);

    expect(screen.queryByText('pim_connector.export.completeness.selector.label')).toBeInTheDocument();
    expect(screen.queryByText('pim_enrich.export.product.filter.completeness.operators.ALL')).toBeInTheDocument();
})

test('it notifies when the operator is changed', async () =>{
    const onOperatorChange = jest.fn();

    await renderWithProviders(<OperatorSelector operator={'ALL'} onChange={onOperatorChange}/>);

    userEvent.click(screen.getByText('pim_connector.export.completeness.selector.label'));
    userEvent.click(screen.getByText('pim_enrich.export.product.filter.completeness.operators.LOWER THAN ON ALL LOCALES'));

    expect(onOperatorChange).toHaveBeenCalledWith('LOWER THAN ON ALL LOCALES');
})
