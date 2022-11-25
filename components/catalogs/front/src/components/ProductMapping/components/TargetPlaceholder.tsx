import React, {FC} from 'react';
import styled from 'styled-components';
import {AttributesIllustration, Placeholder, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const SectionContent = styled.div`
    padding: 30px 0;
    text-align: center;
    width: 100%;
`;

const TableCell = styled(Table.Cell)`
    border-bottom: 0;
`;

export const TargetPlaceholder: FC = () => {
    const translate = useTranslate();

    return (
        <Table.Row>
            <TableCell colSpan={2}>
                <SectionContent>
                    <Placeholder
                        illustration={<AttributesIllustration />}
                        title={translate('akeneo_catalogs.product_mapping.target.placeholder.illustration_title')}
                    ></Placeholder>
                </SectionContent>
            </TableCell>
        </Table.Row>
    );
};
