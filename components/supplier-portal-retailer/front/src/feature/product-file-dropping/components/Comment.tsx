import React from 'react';
import {AkeneoThemedProps, DialogIcon, getColor, onboarderTheme, Pill} from 'akeneo-design-system';
import {useDateFormatter} from '@akeneo-pim-community/shared';
import styled, {css} from 'styled-components';

type Props = {
    outgoing: boolean;
    authorEmail: string;
    content: string;
    createdAt: string;
    isUnread: boolean;
};

const Comment = ({outgoing, authorEmail, content, createdAt, isUnread}: Props) => {
    const dateFormatter = useDateFormatter();

    return (
        <CommentRow>
            <CommentRowContent outgoing={outgoing}>
                <FlexGrow outgoing={outgoing}>
                    <FlexRow>
                        <IconContainer outgoing={outgoing}>
                            {isUnread ? (
                                <StyledPill level="primary" data-testid="unreadIcon" />
                            ) : (
                                <DialogIcon
                                    color={outgoing ? onboarderTheme.color.grey140 : onboarderTheme.color.brand140}
                                />
                            )}
                        </IconContainer>
                        <ContentContainer>
                            <FlexColumn>
                                <AuthorEmailAndDate>
                                    <span>{authorEmail}</span>,&nbsp;
                                    <span>
                                        {dateFormatter(createdAt, {
                                            day: '2-digit',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                        })}
                                    </span>
                                </AuthorEmailAndDate>
                                <Content>"{content}"</Content>
                            </FlexColumn>
                        </ContentContainer>
                    </FlexRow>
                </FlexGrow>
                <FillerContainer></FillerContainer>
            </CommentRowContent>
        </CommentRow>
    );
};

const CommentRow = styled.div`
    flex: 1 1 100%;
    margin-top: 20px;
`;

const IconContainer = styled.div<AkeneoThemedProps & {outgoing: boolean}>`
    margin: 14px 12.5px;
    border-right: 1px solid;
    padding-right: 12.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    ${({outgoing}) =>
        !outgoing
            ? css`
                  color: #2d6486;
              `
            : css`
                  color: ${getColor('grey140')};
              `}
`;

const ContentContainer = styled.div`
    margin-top: 10px;
    margin-bottom: 10px;
    line-height: 15.6px;
`;

const FlexGrow = styled.div<AkeneoThemedProps & {outgoing: boolean}>`
    flex: 200px;
    padding-right: 10px;
    ${({outgoing}) =>
        !outgoing
            ? css`
                  background-color: ${getColor('blue10')};
                  color: #2d6486;
              `
            : css`
                  background-color: ${getColor('grey20')};
                  color: ${getColor('grey140')};
              `}
`;

const FillerContainer = styled.div`
    flex: 100px;
`;

const CommentRowContent = styled.div<AkeneoThemedProps & {outgoing: boolean}>`
    display: flex;
    ${({outgoing}) =>
        !outgoing
            ? css`
                  flex-direction: row-reverse;
              `
            : css`
                  flex-direction: row;
              `}
`;

const AuthorEmailAndDate = styled.div`
    font-weight: bold;
    flex: 1 1 100%;
`;

const FlexRow = styled.div`
    display: flex;
    flex-direction: row;
    word-break: break-word;
`;

const FlexColumn = styled.div`
    display: flex;
    flex-direction: column;
`;

const Content = styled.div`
    flex: 1 1 100%;
`;

const StyledPill = styled(Pill)`
    background-color: ${onboarderTheme.color.brand100};
`;

export {Comment};
