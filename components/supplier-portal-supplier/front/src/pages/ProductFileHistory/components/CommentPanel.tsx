import React, {useState} from 'react';
import styled from 'styled-components';
import {
    AkeneoThemedProps,
    CloseIcon,
    CheckPartialIcon,
    DownloadIcon,
    getColor,
    IconButton,
    PlusIcon,
    SectionTitle,
} from 'akeneo-design-system';
import {ProductFile} from '../model/ProductFile';
import {FormattedMessage, useIntl} from 'react-intl';
import {useDateFormatter} from '../../../utils/date-formatter/use-date-formatter';
import {Comment as CommentReadModel} from '../model/Comment';
import {Comment} from './Comment';

const Panel = styled.div<AkeneoThemedProps & {currentProductFile: ProductFile | null}>`
    width: ${({currentProductFile}) => (currentProductFile ? '30%' : '0')};
    transition-property: width;
    transition-duration: 0.5s;
    box-shadow: 0 0 16px rgba(89, 146, 199, 0.1);
`;

type Props = {
    productFile: ProductFile | null;
    closePanel: () => void;
};

const StyledFilename = styled.div`
    width: auto;
    margin-top: 30px;
    margin-left: 30px;
    color: ${getColor('brand120')};
    font-size: 20px;
    line-height: 24px;
`;

const StyledIconButton = styled(IconButton)`
    color: ${getColor('grey100')};

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const DownloadIconContainer = styled.div`
    margin-top: 25px;
    margin-left: 23.5px;
`;

const CloseIconContainer = styled.div`
    margin-top: 25px;
    margin-right: 25px;
    margin-left: auto;
`;

const FlexRow = styled.div`
    display: flex;
    flex-direction: row;
`;

const UploadDateLabel = styled.div`
    margin-top: 41px;
    margin-left: 30px;
    font-size: 13px;
    line-height: 14px;
    color: ${getColor('grey140')};
`;

const ContributorLabel = styled.div`
    margin-top: 24px;
    margin-left: 30px;
    font-size: 13px;
    line-height: 14px;
    color: ${getColor('grey140')};
`;

const ContributorValue = styled.span`
    color: ${getColor('brand120')};
`;

const UploadDateValue = styled.span`
    color: ${getColor('brand120')};
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

const CommentPanel = ({productFile, closePanel}: Props) => {
    const intl = useIntl();
    const dateFormatter = useDateFormatter();
    const [isExpand, setIsExpand] = useState<boolean>(true);
    if (null === productFile) {
        return <></>;
    }
    let comments = productFile.retailerComments
        .concat(productFile.supplierComments)
        .sort(
            (a: CommentReadModel, b: CommentReadModel) =>
                new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
        );

    return (
        <>
            {productFile ? (
                <Panel currentProductFile={productFile}>
                    <FlexRow>
                        <StyledFilename>{productFile.filename}</StyledFilename>
                        <DownloadIconContainer>
                            <StyledIconButton
                                icon={<DownloadIcon size={20} animateOnHover={true} />}
                                title={intl.formatMessage({
                                    defaultMessage: 'Download',
                                    id: '5q3qC0',
                                })}
                                ghost={'borderless'}
                                href={'/supplier-portal/download-file/' + productFile.identifier}
                            />
                        </DownloadIconContainer>
                        <CloseIconContainer>
                            <StyledIconButton
                                data-testid={'close-panel-icon'}
                                icon={<CloseIcon size={16} />}
                                title={intl.formatMessage({
                                    defaultMessage: 'Close',
                                    id: 'rbrahO',
                                })}
                                ghost={'borderless'}
                                onClick={closePanel}
                            />
                        </CloseIconContainer>
                    </FlexRow>

                    <UploadDateLabel>
                        <FormattedMessage defaultMessage="Upload date: " id="J/ycFm" />
                        <UploadDateValue>
                            {dateFormatter(productFile.uploadedAt, {
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                            })}
                        </UploadDateValue>
                    </UploadDateLabel>

                    <ContributorLabel>
                        <FormattedMessage defaultMessage="Contributor: " id="G/2O1m" />
                        <ContributorValue>{productFile.contributor}</ContributorValue>
                    </ContributorLabel>

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
                </Panel>
            ) : (
                <Panel currentProductFile={productFile} />
            )}
        </>
    );
};

export {CommentPanel};
