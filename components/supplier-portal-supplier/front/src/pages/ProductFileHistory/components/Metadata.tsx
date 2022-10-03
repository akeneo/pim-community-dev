import {DownloadIcon, getColor, IconButton} from 'akeneo-design-system';
import {FormattedMessage, useIntl} from 'react-intl';
import React from 'react';
import styled from 'styled-components';
import {ProductFile} from '../model/ProductFile';
import {useDateFormatter} from '../../../utils/date-formatter/use-date-formatter';

const Metadata = ({productFile}: {productFile: ProductFile}) => {
    const intl = useIntl();
    const dateFormatter = useDateFormatter();

    return (
        <div>
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
                        href={'/supplier-portal/product-file/' + productFile.identifier + '/download'}
                    />
                </DownloadIconContainer>
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
        </div>
    );
};

const FlexRow = styled.div`
    display: flex;
    flex-direction: row;
    width: 80%;
`;

const StyledIconButton = styled(IconButton)`
    color: ${getColor('grey100')};
    width: 30px;

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const StyledFilename = styled.div`
    width: auto;
    margin-top: 30px;
    margin-left: 30px;
    color: ${getColor('brand120')};
    font-size: 20px;
    line-height: 24px;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const DownloadIconContainer = styled.div`
    margin-top: 25px;
    margin-left: 23.5px;
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

export {Metadata};
