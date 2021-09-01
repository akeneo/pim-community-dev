import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, waitForElement} from '@testing-library/react';
import {Permissions} from '@src/connect/components/AppWizardWithSteps/Permissions';

test('The permissions step renders without error', async () => {
    render(<Permissions />);
    await waitForElement(() => screen.getByText('Hello permissions!'));
    expect(screen.getByText('Hello permissions!')).toBeInTheDocument();
});
