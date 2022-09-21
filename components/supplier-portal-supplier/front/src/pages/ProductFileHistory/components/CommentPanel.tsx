import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, CloseIcon, DownloadIcon, getColor, IconButton} from 'akeneo-design-system';
import {ProductFile} from '../model/ProductFile';
import {FormattedMessage, useIntl} from 'react-intl';
import {useDateFormatter} from '../../../utils/date-formatter/use-date-formatter';

const Panel = styled.div<AkeneoThemedProps & {showPanel: boolean}>`
    width: ${({showPanel}) => (showPanel ? '447px' : '0px')};
    transition-property: width;
    transition-duration: 0.5s;
`;

type Props = {
    showPanel: boolean;
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

const CommentPanel = ({showPanel, productFile, closePanel}: Props) => {
    const intl = useIntl();
    const dateFormatter = useDateFormatter();

    return (
        <>
            {productFile && (
                <Panel showPanel={showPanel}>
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
                </Panel>
            )}
        </>
    );
};

export {CommentPanel};
