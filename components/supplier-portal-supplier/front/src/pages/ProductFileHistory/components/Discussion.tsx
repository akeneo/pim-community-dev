import {CheckPartialIcon, getColor, IconButton, PlusIcon, SectionTitle} from 'akeneo-design-system';
import {FormattedMessage, useIntl} from 'react-intl';
import {Comment as CommentReadModel} from '../model/Comment';
import {Comment} from './Comment';
import React, {useState} from 'react';
import styled from 'styled-components';

const Discussion = ({comments}: {comments: CommentReadModel[]}) => {
    const intl = useIntl();
    const [isExpand, setIsExpand] = useState<boolean>(true);
    return (
        <>
            <FlexRow>
                <StyledSectionTitle>
                    <SectionTitle.Title>
                        <FormattedMessage defaultMessage="Comments" id="wCgTu5" />
                    </SectionTitle.Title>
                    <StyledNumberOfComments>{comments.length}</StyledNumberOfComments>
                    <StyledToggleCommentsButton
                        icon={isExpand ? <CheckPartialIcon size={20} /> : <PlusIcon size={20} />}
                        title={
                            isExpand
                                ? intl.formatMessage({
                                      defaultMessage: 'Collapse',
                                      id: 'W/V6+Y',
                                  })
                                : intl.formatMessage({
                                      defaultMessage: 'Expand',
                                      id: '0oLj/t',
                                  })
                        }
                        ghost={'borderless'}
                        onClick={() => setIsExpand(!isExpand)}
                    />
                </StyledSectionTitle>
            </FlexRow>

            {isExpand && (
                <FlexColumn>
                    {comments.map((comment: CommentReadModel, index) => (
                        <Comment
                            key={index}
                            outgoing={comment.outgoing}
                            authorEmail={comment.authorEmail}
                            content={comment.content}
                            createdAt={comment.createdAt}
                        />
                    ))}
                </FlexColumn>
            )}
        </>
    );
};

const FlexRow = styled.div`
    display: flex;
    flex-direction: row;
`;

const StyledSectionTitle = styled(SectionTitle)`
    margin: 38px 30px 0px;
    display: flex;
    justify-content: space-between;
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

const StyledToggleCommentsButton = styled(IconButton)`
    margin-left: auto;
    color: ${getColor('grey100')};

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const FlexColumn = styled.div`
    display: flex;
    flex-direction: column;
    margin-bottom: 30px;
`;

export {Discussion};
