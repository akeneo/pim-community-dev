import React from 'react';
import {AkeneoThemedProps, DialogIcon, getColor, pimTheme} from 'akeneo-design-system';
import {useDateFormatter} from '@akeneo-pim-community/shared';
import styled, {css} from 'styled-components';

type Props = {
    isRetailer: boolean;
    contributorEmail: string;
    content: string;
    createdAt: string;
};

const CommentRow = styled.div`
    flex: 1 1 100%;
    margin-top: 20px;
`;

const IconContainer = styled.div`
    margin: 14px 12.5px;
    border-right: 1px solid;
    padding-right: 12.5px;
`;

const ContentContainer = styled.div`
    margin-top: 10px;
`;

const FlexGrow = styled.div`
    flex: 200px;
    background-color: ${getColor('blue10')};
`;

const FillerContainer = styled.div`
    flex: 100px;
`;

const CommentRowContent = styled.div<AkeneoThemedProps & {isRetailer: boolean}>`
    display: flex;
    ${({isRetailer}) =>
        !isRetailer
            ? css`
                  flex-direction: row-reverse;
              `
            : css`
                  flex-direction: row;
              `}
`;

const ContributorEmailAndDate = styled.div`
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

const Comment = ({isRetailer, contributorEmail, content, createdAt}: Props) => {
    const dateFormatter = useDateFormatter();

    return (
        <CommentRow>
            <CommentRowContent isRetailer={isRetailer}>
                <FlexGrow>
                    <FlexRow>
                        <IconContainer>
                            <DialogIcon color={pimTheme.color.grey140} />
                        </IconContainer>
                        <ContentContainer>
                            <FlexColumn>
                                <ContributorEmailAndDate>
                                    {contributorEmail},&nbsp;
                                    {dateFormatter(createdAt, {
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                    })}
                                </ContributorEmailAndDate>
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
