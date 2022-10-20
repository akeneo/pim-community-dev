import {Button, getColor, Helper, SectionTitle, TextInput} from 'akeneo-design-system';
import {FormattedMessage, useIntl} from 'react-intl';
import {Comment as CommentReadModel} from '../model/Comment';
import {Comment} from './Comment';
import React, {RefObject, useCallback, useEffect, useRef, useState} from 'react';
import styled from 'styled-components';
import {useUserContext} from '../../../contexts';
import {apiFetch} from '../../../api/apiFetch';
import {BadRequestError} from '../../../api/BadRequestError';
import {useQueryClient} from 'react-query';

type Props = {comments: CommentReadModel[]; productFileIdentifier: string};

const COMMENT_MAX_LENGTH = 255;

const Discussion = ({comments, productFileIdentifier}: Props) => {
    const intl = useIntl();
    const [comment, setComment] = useState('');
    const saveCommentRoute = `/supplier-portal/product-file/${productFileIdentifier}/comment`;
    const userContext = useUserContext();
    const [errorCode, setErrorCode] = useState('');
    const queryClient = useQueryClient();
    const commentsBlock: RefObject<HTMLDivElement> = useRef<HTMLDivElement>(null);

    const errorMessages: {[errorCode: string]: string} = {
        empty_comment: intl.formatMessage({
            defaultMessage: 'The comment should not be empty.',
            id: 'X0ZkXi',
        }),
        comment_too_long: intl.formatMessage({
            defaultMessage: 'The comment should not exceed 255 characters.',
            id: 'bKKx33',
        }),
        max_comments_limit_reached: intl.formatMessage({
            defaultMessage: "You've reached the comment limit.",
            id: '5GJDDs',
        }),
    };

    const updateComment = (comment: string) => {
        setComment(comment);
        setErrorCode('');
    };

    useEffect(() => {
        const height: number = commentsBlock?.current?.scrollHeight ?? 0;
        if (commentsBlock && commentsBlock.current) {
            commentsBlock?.current?.scrollTo({top: height});
        }
    }, [comments]);

    const saveComment = useCallback(
        async event => {
            event.preventDefault();
            try {
                await apiFetch(saveCommentRoute, {
                    method: 'POST',
                    headers: {'Content-type': 'application/json'},
                    body: JSON.stringify({
                        content: comment,
                        authorEmail: userContext.user?.email,
                    }),
                });
                setErrorCode('');
                setComment('');
                await queryClient.invalidateQueries('fetchProductFiles');
            } catch (error) {
                if (error instanceof BadRequestError) {
                    setErrorCode(error.data);
                }
            }
        },
        [saveCommentRoute, comment, userContext.user?.email, queryClient]
    );

    return (
        <>
            <FlexRow>
                <StyledSectionTitle>
                    <SectionTitle.Title>
                        <FormattedMessage defaultMessage="Comments" id="wCgTu5" />
                    </SectionTitle.Title>
                    <StyledNumberOfComments>{comments.length}</StyledNumberOfComments>
                </StyledSectionTitle>
            </FlexRow>

            <Comments ref={commentsBlock}>
                <div>
                    {comments.map((comment: CommentReadModel, index) => (
                        <Comment
                            key={index}
                            outgoing={comment.outgoing}
                            authorEmail={comment.authorEmail}
                            content={comment.content}
                            createdAt={comment.createdAt}
                        />
                    ))}
                </div>
            </Comments>

            <FlexColumn>
                <SendCommentForm onSubmit={saveComment} role="form">
                    <TextInput
                        onChange={updateComment}
                        value={comment}
                        placeholder={intl.formatMessage({
                            defaultMessage: 'Say something about this file',
                            id: '6DyDNI',
                        })}
                    />
                    <Button
                        type="submit"
                        level="secondary"
                        onClick={saveComment}
                        disabled={'' === comment || COMMENT_MAX_LENGTH < comment.length}
                    >
                        <FormattedMessage defaultMessage="Send" id="9WRlF4" />
                    </Button>
                </SendCommentForm>
                {errorCode && (
                    <StyledHelper inline level="error">
                        {errorMessages[errorCode]}
                    </StyledHelper>
                )}
                {COMMENT_MAX_LENGTH < comment.length && (
                    <StyledHelper inline level="error">
                        <FormattedMessage defaultMessage="The comment should not exceed 255 characters." id="bKKx33" />
                    </StyledHelper>
                )}
            </FlexColumn>
        </>
    );
};

const FlexRow = styled.div`
    display: flex;
    flex-direction: row;
    gap: 10px;
`;

const SendCommentForm = styled.form`
    display: flex;
    flex-direction: row;
    margin: 0 30px 0 30px;
    align-items: center;
    padding: 10px 0 0 0;
    border-top: 1px solid ${getColor('grey20')};
    gap: 10px;
`;

const StyledSectionTitle = styled(SectionTitle)`
    margin: 38px 30px 0;
    display: flex;
    border-bottom: none;
    border-top: solid 1px #f0f1f3;
    padding-top: 15px;
`;

const StyledNumberOfComments = styled.span`
    color: #355777;
    border: 1px solid #5992c7;
    border-radius: 2px;
    line-height: 16px;
    padding: 0 6px;
    font-size: 11px;
`;

const FlexColumn = styled.div`
    display: flex;
    flex-direction: column;
    margin-bottom: 30px;
`;

const Comments = styled.div`
    display: flex;
    flex-direction: column-reverse;
    margin-bottom: 30px;
    flex: 1;
    overflow-y: auto;
`;

const StyledHelper = styled(Helper)`
    margin: 5px 0 0 30px;
`;

export {Discussion};
