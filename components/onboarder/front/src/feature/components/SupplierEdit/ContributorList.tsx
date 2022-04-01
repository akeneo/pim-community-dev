import React, {useEffect, useState} from 'react';
import {useTranslate, useDebounceCallback} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeleteIcon, Field, Helper, Search, Table, TagInput, Button} from 'akeneo-design-system';
import {ContributorEmail, Supplier} from '../../models';
import {useFilteredContributors} from '../../hooks';
import {EmptyContributorList} from '../EmptyContributorList';

type Props = {
    supplier: Supplier;
    setContributors: (value: ContributorEmail[]) => void;
};

const ContributorList = ({supplier, setContributors}: Props) => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');
    const [newContributors, setNewContributors] = useState<string[]>([]);
    const {filteredContributors, search} = useFilteredContributors(supplier.contributors);

    const debouncedSearch = useDebounceCallback(search, 300);

    const onSearch = (searchValue: string) => {
        setSearchValue(searchValue);
        debouncedSearch(searchValue);
    };

    const onChangeNewContributors = (newContributors: string[]) => {
        setNewContributors(newContributors.filter(contributorEmail => isValidEmail(contributorEmail)));
    };

    const onClickAdd = () => {
        setContributors(supplier.contributors.concat(newContributors));
        setNewContributors([]);
    };

    const removeContributor = (emailToRemove: ContributorEmail) => {
        setContributors(supplier.contributors.filter(email => email !== emailToRemove));
    };

    const isValidEmail = (email: string) => {
        const emailRegex = /\S+@\S+\.\S+/;
        return emailRegex.test(email);
    };

    useEffect(() => {
        if ('' !== searchValue) {
            search(searchValue);
        }
    }, [supplier.contributors]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <TabContainer>
            <Helper level="info">{translate('onboarder.supplier.supplier_edit.contributors_form.info')}</Helper>

            <Field label={translate('onboarder.supplier.supplier_edit.contributors_form.add_contributors')}>
                <FieldContent>
                    <TagInputContainer>
                        <TagInput onChange={onChangeNewContributors} value={newContributors} />
                    </TagInputContainer>
                    <Button level="tertiary" onClick={onClickAdd}>
                        {translate('onboarder.supplier.supplier_edit.contributors_form.add_button')}
                    </Button>
                </FieldContent>
            </Field>

            {0 === filteredContributors.length && '' === searchValue && <EmptyContributorList />}
            {(0 < filteredContributors.length || '' !== searchValue) && (
                <>
                    <Search
                        onSearchChange={onSearch}
                        searchValue={searchValue}
                        placeholder={translate(
                            'onboarder.supplier.supplier_edit.contributors_form.search_by_email_address'
                        )}
                    >
                        <Search.ResultCount>
                            {translate(
                                'onboarder.supplier.supplier_edit.contributors_form.result_counter',
                                {count: filteredContributors.length},
                                filteredContributors.length
                            )}
                        </Search.ResultCount>
                    </Search>

                    <Table>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_edit.contributors_form.columns.email')}
                            </Table.HeaderCell>
                            <Table.HeaderCell />
                        </Table.Header>
                        <Table.Body>
                            {filteredContributors.map(email => (
                                <Table.Row key={email} data-testid={email}>
                                    <Table.Cell>{email}</Table.Cell>
                                    <DeleteCell>
                                        <DeleteIcon onClick={() => removeContributor(email)} />
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
        margin: 0 0 20px 0;
        max-width: none;
    }
`;

const DeleteCell = styled(Table.ActionCell)`
    width: 50px;
`;

const FieldContent = styled.div`
    display: flex;
    align-items: center;
`;

const TagInputContainer = styled.div`
    margin-right: 10px;
    width: 460px;
`;

export {ContributorList};
