import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {OperatorSelector} from "./OperatorSelector";
import React from "react";

test('it displays the selected operator', () =>{
    renderWithProviders(<OperatorSelector operator={'ALL'} onChange={() => {}}/>);

    expect(screen.queryByText('pim_connector.export.completeness.selector.label')).toBeInTheDocument();
})
