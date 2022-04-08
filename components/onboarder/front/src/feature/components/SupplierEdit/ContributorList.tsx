import React, {useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeleteIcon, Field, Helper, Search, Table, TagInput, Button} from 'akeneo-design-system';
import {ContributorEmail, isValidEmail} from '../../models';
import {useFilteredContributors} from '../../hooks';
import {EmptyContributorList} from '../EmptyContributorList';

type Props = {
    contributors: ContributorEmail[];
    setContributors: (value: ContributorEmail[]) => void;
};

const ContributorList = ({contributors, setContributors}: Props) => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');
    const [newContributors, setNewContributors] = useState<string[]>([]);
    const filteredContributors = useFilteredContributors(contributors, searchValue);

    const onChangeNewContributors = (newContributors: string[]) => {
        setNewContributors(newContributors);
    };

    const handleNewContributorsAdd = () => {
        setContributors([
            ...contributors,
            ...newContributors.filter(contributorEmail => isValidEmail(contributorEmail)),
        ]);
        setNewContributors([]);
    };

    const removeContributor = (emailToRemove: ContributorEmail) => {
        setContributors(contributors.filter(email => email !== emailToRemove));
    };

    return (
        <TabContainer>
            <Helper level="info">{translate('onboarder.supplier.supplier_edit.contributors_form.info')}</Helper>

            <Field label={translate('onboarder.supplier.supplier_edit.contributors_form.add_contributors')}>
                <FieldContent>
                    <TagInputContainer>
                        <TagInput onChange={onChangeNewContributors} value={newContributors} />
                    </TagInputContainer>
                    <Button level="tertiary" onClick={handleNewContributorsAdd}>
                        {translate('onboarder.supplier.supplier_edit.contributors_form.add_button')}
                    </Button>
                </FieldContent>
            </Field>

            {0 === filteredContributors.length && '' === searchValue && <EmptyContributorList />}
            {(0 < filteredContributors.length || '' !== searchValue) && (
                <>
                    <Search
                        onSearchChange={setSearchValue}
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
