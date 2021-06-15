import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {LocalesSelector} from "./LocalesSelector";
import React from "react";

test('it displays the selected locales', () =>{
    renderWithProviders(<LocalesSelector locales={['en_US']} onChange={() => {}}/>);

    expect(screen.queryByText('pim_connector.export.completeness.selector.label')).toBeInTheDocument();
})
