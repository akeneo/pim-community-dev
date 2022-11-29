import React from 'react';
import {ProductFile} from '../models/ProductFile';
import {DateIcon, getColor, pimTheme, SectionTitle, SupplierIcon, UserIcon} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type Props = {
    productFile: ProductFile;
};

const StyledSectionTitle = styled(SectionTitle)`
    margin-top: 20px;
`;

const FlexMainContainer = styled.div`
    display: flex;
    margin-top: 25px;
`;

const FirstColumn = styled.div`
    display: flex;
    flex-direction: row;
    flex: 0 0 40%;
`;

const SecondColumn = styled.div`
    display: flex;
    flex-direction: row;
    flex: 0 0 60%;
`;

const FlexColumnContainer = styled.div`
    display: flex;
    flex-direction: column;
`;

const IconContainer = styled.div`
    border-right: 1px solid ${getColor('grey60')};
    padding: 17px 27.5px;
`;

const StyledLabel = styled.div`
    margin-left: 20px;
    color: ${getColor('grey140')};
`;

const StyledValue = styled.div`
    margin-left: 20px;
    margin-top: 11px;
    color: ${getColor('brand100')};
`;

const GeneralInformation = ({productFile}: Props) => {
    const translate = useTranslate();

    return (
        <>
            <StyledSectionTitle>
                <SectionTitle.Title>
                    {translate('supplier_portal.product_file_dropping.supplier_files.general_information.title')}
                </SectionTitle.Title>
            </StyledSectionTitle>

            <FlexMainContainer>
                <FirstColumn>
                    <IconContainer>
                        <SupplierIcon color={pimTheme.color.grey100} />
                    </IconContainer>
                    <FlexColumnContainer>
                        <StyledLabel>
                            {translate(
                                'supplier_portal.product_file_dropping.supplier_files.general_information.supplier'
                            )}
                        </StyledLabel>
                        <StyledValue>{productFile.supplierLabel}</StyledValue>
                    </FlexColumnContainer>
                </FirstColumn>
                <SecondColumn>
                    <IconContainer>
                        <UserIcon color={pimTheme.color.grey100} />
                    </IconContainer>
                    <FlexColumnContainer>
                        <StyledLabel>
                            {translate(
                                'supplier_portal.product_file_dropping.supplier_files.general_information.contributor'
                            )}
                        </StyledLabel>
                        <StyledValue>{productFile.contributor}</StyledValue>
                    </FlexColumnContainer>
                </SecondColumn>
            </FlexMainContainer>
            <FlexMainContainer>
                <FirstColumn>
                    <IconContainer>
                        <DateIcon color={pimTheme.color.grey100} />
                    </IconContainer>
                    <FlexColumnContainer>
                        <StyledLabel>
                            {translate(
                                'supplier_portal.product_file_dropping.supplier_files.general_information.upload_date'
                            )}
                        </StyledLabel>
                        <StyledValue>{productFile.uploadedAt}</StyledValue>
                    </FlexColumnContainer>
                </FirstColumn>
                <SecondColumn>
                    <IconContainer>
                        <DateIcon color={pimTheme.color.grey100} />
                    </IconContainer>
                    <FlexColumnContainer>
                        <StyledLabel>
                            {translate(
                                'supplier_portal.product_file_dropping.supplier_files.general_information.import_date'
                            )}
                        </StyledLabel>
                        <StyledValue>
                            {null !== productFile.importedAt
                                ? productFile.importedAt
                                : translate(
                                      'supplier_portal.product_file_dropping.supplier_files.general_information.not_imported_yet'
                                  )}
                        </StyledValue>
                    </FlexColumnContainer>
                </SecondColumn>
            </FlexMainContainer>
        </>
    );
};

export {GeneralInformation};
