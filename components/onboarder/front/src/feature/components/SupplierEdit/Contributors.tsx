import React, {useState} from 'react';
import {useTranslate, useDebounceCallback} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeleteIcon, Helper, Search, Table} from 'akeneo-design-system';
import {Contributor} from '../../models';
import {useFilteredContributors} from "../../hooks";

type Props = {
    contributors: Contributor[];
};

const Contributors = ({contributors}: Props) => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');
    const {filteredContributors, search} = useFilteredContributors(contributors);

    const debouncedSearch = useDebounceCallback(search, 300);

    const onSearch = (searchValue: string) => {
        setSearchValue(searchValue);
        debouncedSearch(searchValue);
    };

    return (
        <TabContainer>
            <Helper level="info">{translate('onboarder.supplier.supplier_edit.contributors_form.info')}</Helper>

            <Search
                onSearchChange={onSearch}
                searchValue={searchValue}
                placeholder={translate('onboarder.supplier.supplier_edit.contributors_form.search_by_email_address')}
            />

            <Table>
                <Table.Header>
                    <Table.HeaderCell>
                        {translate('onboarder.supplier.supplier_edit.contributors_form.columns.email')}
                    </Table.HeaderCell>
                    <Table.HeaderCell/>
                </Table.Header>
                <Table.Body>
                    {filteredContributors.map((contributor: Contributor) => (
                        <Table.Row key={contributor.identifier} data-testid={contributor.email}>
                            <Table.Cell>{contributor.email}</Table.Cell>
                            <DeleteCell>
                                <DeleteIcon/>
                            </DeleteCell>
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

const DeleteCell = styled(Table.ActionCell)`
  width: 50px;
`;

export {Contributors};
