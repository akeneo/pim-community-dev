import React, {useEffect, useState} from 'react';
import {useRoute, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeleteIcon, Field, Helper, Search, Table, TagInput, Button} from 'akeneo-design-system';
import {ContributorEmail, isValidEmail} from '../../models';
import {useFilteredContributors} from '../../hooks';
import {EmptyContributorList} from '../EmptyContributorList';

type Props = {
    supplierIdentifier: string;
    contributors: ContributorEmail[];
    setContributors: (value: ContributorEmail[]) => void;
};

const ContributorList = ({supplierIdentifier, contributors, setContributors}: Props) => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');
    const [newContributors, setNewContributors] = useState<string[]>([]);
    const filteredContributors = useFilteredContributors(contributors, searchValue);
    const [displayInvalidContributorEmailsWarning, setDisplayInvalidContributorEmailsWarning] = useState(false);
    const [contributorsBelongingToAnotherSupplier, setContributorsBelongingToAnotherSupplier] = useState<string[]>([]);
    const getContributorsBelongingToAnotherSupplierRoute = useRoute(
        'onboarder_serenity_get_supplier_contributors_belonging_to_another_supplier',
        {supplierIdentifier, emails: JSON.stringify(contributors)},
    );

    const onChangeNewContributors = (newContributors: string[]) => {
        const notValidContributorEmails = newContributors.filter(email => !isValidEmail(email))

        setDisplayInvalidContributorEmailsWarning(0 < notValidContributorEmails.length);

        setNewContributors(newContributors);
    };

    const handleNewContributorsAdd = () => {
        const validContributorEmails = newContributors
            .filter(contributorEmail => isValidEmail(contributorEmail))
            .filter(contributorEmail => contributorEmail.length <= 255);

        setNewContributors([]);

        const updatedContributors = Array.from(new Set([...contributors, ...validContributorEmails]));
        if (JSON.stringify(updatedContributors) !== JSON.stringify(contributors)) {
            setContributors(updatedContributors);
            return;
        }
    };

    const removeContributor = (emailToRemove: ContributorEmail) => {
        setContributors(contributors.filter(email => email !== emailToRemove));
    };

    useEffect(() => {
        if (contributors.length === 0) {
            return;
        }
        (async () => {
            const response = await fetch(getContributorsBelongingToAnotherSupplierRoute, {method: 'GET'});
            setContributorsBelongingToAnotherSupplier(await response.json());
        })();
    }, [contributors, getContributorsBelongingToAnotherSupplierRoute]);

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
                {
                    displayInvalidContributorEmailsWarning && (
                        <Helper level="warning">
                            {translate('onboarder.supplier.supplier_edit.contributors_form.invalid_emails_warning')}
                        </Helper>
                    )
                }
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

                    {contributorsBelongingToAnotherSupplier.length > 0 &&
                        <StyledHelper level={"warning"}>
                            {translate('onboarder.supplier.supplier_edit.contributors_form.emails_belonging_to_other_suppliers_warning')}
                        </StyledHelper>}

                    <Table hasWarnedRows={contributorsBelongingToAnotherSupplier.length > 0}>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_edit.contributors_form.columns.email')}
                            </Table.HeaderCell>
                            <Table.HeaderCell />
                        </Table.Header>
                        <Table.Body>
                            {filteredContributors.map(email => (
                                <Table.Row key={email} data-testid={email} hasWarning={contributorsBelongingToAnotherSupplier.includes(email)}>
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

const StyledHelper = styled(Helper)`
    margin: 0;
`;

export {ContributorList};
