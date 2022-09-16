import React from 'react';
import {act, fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Discussion} from './Discussion';
import userEvent from '@testing-library/user-event';

const productFile = {
    identifier: '037455b4-24a3-4404-a721-aca6f06d6293',
    originalFilename: 'file.xlsx',
    uploadedAt: '09/22/2022, 04:08 AM',
    authorEmail: 'julia@akeneo.com',
    supplier: 'ffa51317-e609-481e-b6a3-63991b4e6dbe',
    retailerComments: [],
    supplierComments: [],
};

test('it does not enable the send button if the comment textarea is not fulfilled', () => {
    renderWithProviders(
        <Discussion
            productFile={productFile}
            saveComment={(content: string, authorEmail: string) => {
                return {};
            }}
            validationErrors={[]}
        />
    );

    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.submit_button_label')
    ).toBeDisabled();
});

test('it enables the send button enabled if the comment textarea is fulfilled', () => {
    renderWithProviders(
        <Discussion
            productFile={productFile}
            saveComment={(content: string, authorEmail: string) => {
                return {};
            }}
            validationErrors={[]}
        />
    );

    const commentInput = screen.getByLabelText(
        'supplier_portal.product_file_dropping.supplier_files.discussion.comment_input_label'
    );

    fireEvent.change(commentInput, {target: {value: 'This file is outdated, please send 2022 version instead.'}});

    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.submit_button_label')
    ).not.toBeDisabled();
});

test('it can save a comment', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => {},
    }));

    const saveComment = jest.fn();

    renderWithProviders(<Discussion productFile={productFile} saveComment={saveComment} validationErrors={[]} />);

    userEvent.type(
        screen.getByLabelText('supplier_portal.product_file_dropping.supplier_files.discussion.comment_input_label'),
        'This file is outdated, please send 2022 version instead.'
    );

    await act(async () => {
        userEvent.click(
            screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.submit_button_label')
        );
    });

    expect(saveComment).toHaveBeenCalledTimes(1);
});
