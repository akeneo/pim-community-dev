import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Comment as CommentReadModel} from '../models/read/Comment';
import {Comment} from './Comment';

const StyledSectionTitle = styled(SectionTitle)`
    margin-top: 43px;
`;

const FlexColumn = styled.div`
    display: flex;
    flex-direction: column;
    margin-bottom: 30px;
`;

type Props = {
    comments: CommentReadModel[];
};

const CommentList = ({comments}: Props) => {
    const translate = useTranslate();

    if (0 === comments.length) {
        return null;
    }

    return (
        <>
            <StyledSectionTitle>
                <SectionTitle.Title>
                    {translate('supplier_portal.product_file_dropping.supplier_files.discussion.discussion_title')}
                </SectionTitle.Title>
            </StyledSectionTitle>
            <FlexColumn>
                {comments
                    .sort(
                        (a: CommentReadModel, b: CommentReadModel) =>
                            new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
                    )
                    .map((comment: CommentReadModel, index) => (
                        <Comment
                            key={index}
                            outgoing={comment.outgoing}
                            authorEmail={comment.authorEmail}
                            content={comment.content}
                            createdAt={comment.createdAt}
                            isUnread={comment.isUnread}
                        />
                    ))}
            </FlexColumn>
        </>
    );
};

export {CommentList};
