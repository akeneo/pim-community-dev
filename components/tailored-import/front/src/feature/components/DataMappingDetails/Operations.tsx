import React from 'react';
import { DataMapping } from "../../models";
import styled from "styled-components";
import { SectionTitle, Preview } from "akeneo-design-system";
import { useTranslate } from "@akeneo-pim-community/shared";

type OperationsProps = {
    dataMapping: DataMapping
}

const OperationsContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 10px;
`;

const Operations = ({ dataMapping }: OperationsProps) => {
    const translate = useTranslate();

    return (
        <OperationsContainer>
            <SectionTitle sticky={0}>
                <SectionTitle.Title level="secondary">
                    {translate('akeneo.tailored_import.data_mapping.operations.title')}
                </SectionTitle.Title>
            </SectionTitle>
            {dataMapping.sample_data.length > 0 &&
                <Preview title={translate("akeneo.tailored_import.data_mapping.preview.title")}>
                    {
                        dataMapping.sample_data.map(
                            (sampleData) => (
                                <Preview.Row>{sampleData}</Preview.Row>
                            )
                        )
                    }
                </Preview>
            }

        </OperationsContainer>
    );
}

export { Operations }