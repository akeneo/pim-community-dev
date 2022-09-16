import React, {useState} from 'react';
import {Button, Field, Helper, TextAreaInput} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from "styled-components";
import {CommentList} from "./CommentList";
import {ProductFile} from "../models/ProductFile";

const StickyContainer = styled.div`
  flex-shrink: 0;
`

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
`

const Form = styled.form`
  margin-top: 30px;
  display: flex;
  flex-direction: row;
`

type Props = {
    productFile: ProductFile;
    saveComment: (content: string, authorEmail: string) => {};
}

const Discussion = ({productFile, saveComment}: Props) => {
    const translate = useTranslate();
    const [comment, setComment] = useState<string>('');
    const authorEmail = useUserContext().get('email');
    const onSubmit = async (event: any) => {
        event.preventDefault();
        saveComment(comment, authorEmail);
    };
    const isSubmitButtonDisabled = '' === comment;

    return (
        <>
            <StickyContainer>
                <Helper level="info">{translate('supplier_portal.product_file_dropping.supplier_files.discussion.info')}</Helper>
                <Form method="POST" onSubmit={onSubmit} role="form">
                    <Field label={translate('supplier_portal.product_file_dropping.supplier_files.discussion.comment_input_label')}>
                        <StyledTextAreaInput readOnly={false} value={comment} onChange={setComment}/>
                    </Field>
                    <StyledButton level="tertiary" type="submit" disabled={isSubmitButtonDisabled} onClick={onSubmit}>
                        {translate('supplier_portal.product_file_dropping.supplier_files.discussion.submit_button_label')}
                    </StyledButton>
                </Form>
            </StickyContainer>
            <CommentList comments={productFile.retailerComments.concat(productFile.supplierComments)} />
        </>
    );
};

export {Discussion};
