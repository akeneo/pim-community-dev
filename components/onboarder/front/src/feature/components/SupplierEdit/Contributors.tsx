import React, {useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Helper, Search, Table} from 'akeneo-design-system';
import {Contributor} from '../../models';

type Props = {
    contributors: Contributor[];
};

type ContributorRow = {
    email: string;
};

const Contributors = ({contributors}: Props) => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');

    return (
        <TabContainer>
            <Helper level="info">{translate('onboarder.supplier.supplier_edit.contributors_form.info')}</Helper>

            <Search
                onSearchChange={setSearchValue}
                searchValue={searchValue}
                placeholder={translate('onboarder.supplier.supplier_edit.contributors_form.search_by_email_address')}
            />

            <Table>
                <Table.Header>
                    <Table.HeaderCell>
                        {translate('onboarder.supplier.supplier_edit.contributors_form.columns.email')}
                    </Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                    {contributors.map((contributor: ContributorRow) => (
                        <Table.Row key={contributor.email} data-testid={contributor.email}>
                            <Table.Cell>{contributor.email}</Table.Cell>
                        </Table.Row>
                    ))}
                </Table.Body>
            </Table>
        </TabContainer>
    );
};

const TabContainer = styled.div`
    & > * {
        margin: 0 10px 20px 0;
    }
`;

export {Contributors};
