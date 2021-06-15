import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CompletenessFilter, Operator} from './CompletenessFilter';

test.each([
    {operator: 'ALL', shouldAppear: false},
    {operator: 'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE', shouldAppear: true},
    {operator: 'GREATER OR EQUALS THAN ON ALL LOCALES', shouldAppear: true},
    {operator: 'LOWER THAN ON ALL LOCALES', shouldAppear: true},
])
('it displays the locale selector depending on the operator', ({operator, shouldAppear}: {operator: Operator, shouldAppear: boolean}) => {
    renderWithProviders(<CompletenessFilter operator={operator} locales={['fr_FR', 'en_US']} onOperatorChange={() => {}} onLocalesChange={() => {}}/>);

    expect(screen.queryByText('pim_connector.export.completeness.selector.label')).toBeInTheDocument();

    if (shouldAppear) {
        expect(screen.queryByText('pim_common.label')).toBeInTheDocument();
    } else {
        expect(screen.queryByText('pim_common.label')).not.toBeInTheDocument();
    }
});
