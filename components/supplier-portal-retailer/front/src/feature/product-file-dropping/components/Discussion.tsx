import React, {useEffect, useState} from 'react';
import {Button, Field, Helper, TextAreaInput} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRoute, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {CommentList} from './CommentList';
import {ProductFile} from '../models/ProductFile';

const maxLengthComment = 255;
const maxNumberOfComments = 50;

type Props = {
    productFile: ProductFile;
    saveComment: (content: string, authorEmail: string) => {};
    validationError: string | null;
};

const Discussion = ({productFile, saveComment, validationError}: Props) => {
    const translate = useTranslate();
    const [comment, setComment] = useState<string>('');
    const [commentLength, setCommentLength] = useState<number>(0);
    const authorEmail = useUserContext().get('email');
    const productFileIdentifier = productFile.identifier;
    const markCommentsAsReadRoute = useRoute('supplier_portal_retailer_mark_comments_as_read', {productFileIdentifier});
    const notify = useNotify();
    const onSubmit = async (event: any) => {
        event.preventDefault();
        saveComment(comment, authorEmail);
        setComment('');
    };
    const handleChange = (value: any) => {
        setCommentLength(value.length);
        setComment(value);
    };
    const isCommentMaxLengthReached = maxLengthComment < commentLength;
    const isMaxNumberOfCommentsReached =
        maxNumberOfComments <= productFile.retailerComments.concat(productFile.supplierComments).length;
    const isSubmitButtonDisabled = '' === comment || isCommentMaxLengthReached;

    useEffect(() => {
        (async () => {
            const response = await fetch(markCommentsAsReadRoute, {method: 'POST'});
            if (!response.ok) {
                notify(
                    NotificationLevel.ERROR,
                    translate(
                        'supplier_portal.product_file_dropping.supplier_files.discussion.product_file_does_not_exist_anymore'
                    )
                );
            }
        })();
    }, [markCommentsAsReadRoute, notify, translate]);

    return (
        <>
            <StickyContainer>
                <Helper level={!isMaxNumberOfCommentsReached ? 'info' : 'warning'}>
                    {!isMaxNumberOfCommentsReached
                        ? translate('supplier_portal.product_file_dropping.supplier_files.discussion.info')
                        : translate(
                              'supplier_portal.product_file_dropping.supplier_files.discussion.max_number_of_comments_reached'
                          )}
                </Helper>
                <Form method="POST" onSubmit={onSubmit} role="form">
                    <Field
                        label={translate(
                            'supplier_portal.product_file_dropping.supplier_files.discussion.comment_input_label'
                        )}
                    >
                        <StyledTextAreaInput readOnly={false} value={comment} onChange={handleChange} />
                        {null !== validationError && <Helper level="error">{translate(validationError)}</Helper>}
                        {isCommentMaxLengthReached && (
                            <Helper level="error">
                                {translate(
                                    'supplier_portal.product_file_dropping.supplier_files.discussion.max_comment_length_reached'
                                )}
                            </Helper>
                        )}
                    </Field>
                    <StyledButton level="tertiary" type="submit" disabled={isSubmitButtonDisabled} onClick={onSubmit}>
                        {translate(
                            'supplier_portal.product_file_dropping.supplier_files.discussion.submit_button_label'
                        )}
                    </StyledButton>
                </Form>
            </StickyContainer>
            <CommentList comments={productFile.retailerComments.concat(productFile.supplierComments)} />
        </>
    );
};

const StickyContainer = styled.div`
    flex-shrink: 0;
`;

const StyledButton = styled(Button)`
    margin-top: 23px;
    margin-left: 10px;
`;

const StyledTextAreaInput = styled(TextAreaInput)`
    width: 460px;
    height: 100px;
    flex: none;
    order: 0;
    align-self: stretch;
    flex-grow: 1;
`;

const Form = styled.form`
    margin-top: 30px;
    display: flex;
    flex-direction: row;
`;

export {Discussion};
