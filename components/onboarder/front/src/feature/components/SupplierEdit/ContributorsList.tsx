import React, {useState} from 'react';
import {useTranslate, useDebounceCallback} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeleteIcon, Helper, Search, Table} from 'akeneo-design-system';
import {Contributors} from '../../models';
import {useFilteredContributors} from '../../hooks';
import {EmptyContributorList} from '../EmptyContributorList';

type Props = {
    contributors: Contributors;
};

const ContributorsList = ({contributors}: Props) => {
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

            {0 === Object.keys(filteredContributors).length && '' === searchValue && <EmptyContributorList />}
            {(0 < Object.keys(filteredContributors).length || '' !== searchValue) && (
                <>
                    <Search
                        onSearchChange={onSearch}
                        searchValue={searchValue}
                        placeholder={translate(
                            'onboarder.supplier.supplier_edit.contributors_form.search_by_email_address'
                        )}
                    />

                    <Table>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_edit.contributors_form.columns.email')}
                            </Table.HeaderCell>
                            <Table.HeaderCell />
                        </Table.Header>
                        <Table.Body>
                            {Object.entries(filteredContributors).map(([id, email]) => (
                                <Table.Row key={`contributor-${id}`} data-testid={email}>
                                    <Table.Cell>{email}</Table.Cell>
                                    <DeleteCell>
                                        <DeleteIcon />
                                    </DeleteCell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                </>
            )}
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

export {ContributorsList};
