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

test('it does not enable the send button if the comment textarea exceeds 255 characters', () => {
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

    fireEvent.change(commentInput, {
        target: {
            value: "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.",
        },
    });

    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.submit_button_label')
    ).toBeDisabled();
});

test('it does not enable the send button if the number max of comments is reached', () => {
    for (let i = 0; 50 > i; i++) {
        productFile.retailerComments.push({
            content: 'foo',
            authorEmail: 'julia@roberts.com',
            createdAt: '09/22/2022, 04:08 AM',
        });
    }
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
    expect(
        screen.getByText(
            'supplier_portal.product_file_dropping.supplier_files.discussion.max_number_of_comments_reached'
        )
    ).toBeInTheDocument();
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
