import React from 'react';
import {AkeneoThemedProps, DialogIcon, getColor} from 'akeneo-design-system';
import {useDateFormatter} from '../../../utils/date-formatter/use-date-formatter';
import styled from 'styled-components';

type Props = {
    outgoing: boolean;
    authorEmail: string;
    content: string;
    createdAt: string;
};

const CommentRow = styled.div`
    flex: 1 1 100%;
    margin-top: 10px;
`;

const IconContainer = styled.div<AkeneoThemedProps & {outgoing: boolean}>`
    margin: 14px 12.5px;
    border-right: 1px solid;
    padding-right: 12.5px;
    color: ${({outgoing}) => (outgoing ? getColor('grey140') : '#2d6486')};
`;

const ContentContainer = styled.div`
    margin-top: 10px;
    margin-bottom: 10px;
    line-height: 15.6px;
`;

const FlexGrow = styled.div<AkeneoThemedProps & {outgoing: boolean}>`
    flex: 320px;
    padding-right: 10px;
    color: ${({outgoing}) => (outgoing ? getColor('grey140') : getColor('brand120'))};
    background-color: ${({outgoing}) => (outgoing ? getColor('grey20') : getColor('blue10'))};
`;

const FillerContainer = styled.div`
    flex: 100px;
`;

const CommentRowContent = styled.div<AkeneoThemedProps & {outgoing: boolean}>`
    display: flex;
    flex-direction: ${({outgoing}) => (outgoing ? 'row-reverse' : 'row')};
    margin-left: ${({outgoing}) => (outgoing ? '0' : '30px')};
    margin-right: ${({outgoing}) => (outgoing ? '30px' : '0')};
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

const Comment = ({outgoing, authorEmail, content, createdAt}: Props) => {
    const dateFormatter = useDateFormatter();

    return (
        <CommentRow>
            <CommentRowContent outgoing={outgoing}>
                <FlexGrow outgoing={outgoing}>
                    <FlexRow>
                        <IconContainer outgoing={outgoing}>
                            <DialogIcon color={outgoing ? '#11324d' : '#3c86b3'} />
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

export {Comment};
