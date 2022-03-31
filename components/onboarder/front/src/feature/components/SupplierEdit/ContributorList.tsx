import React, {useEffect, useState} from 'react';
import {useTranslate, useDebounceCallback} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeleteIcon, Field, Helper, Search, Table, TagInput, Button} from 'akeneo-design-system';
import {ContributorEmail} from '../../models';
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
    const {filteredContributors, search} = useFilteredContributors(contributors);

    const debouncedSearch = useDebounceCallback(search, 300);

    const onSearch = (searchValue: string) => {
        setSearchValue(searchValue);
        debouncedSearch(searchValue);
    };

    const onChangeNewContributors = (newContributors: string[]) => {
      setNewContributors(newContributors);
    };

    const onClickAdd = () => {
      setContributors(contributors.concat(newContributors));
      setNewContributors([]);
    };

    useEffect(() => {
      if ('' !== searchValue) {
        search(searchValue);
      }
    }, [contributors]);

    return (
        <TabContainer>
            <Helper level="info">{translate('onboarder.supplier.supplier_edit.contributors_form.info')}</Helper>

            <Field label={translate('onboarder.supplier.supplier_edit.contributors_form.add_contributors')}>
              <FieldContent>
                <TagInputContainer>
                  <TagInput
                    onChange={onChangeNewContributors}
                    value={newContributors}
                  />
                </TagInputContainer>
                <Button level="tertiary" onClick={onClickAdd}>{translate('onboarder.supplier.supplier_edit.contributors_form.add_button')}</Button>
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
                    />

                    <Table>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_edit.contributors_form.columns.email')}
                            </Table.HeaderCell>
                            <Table.HeaderCell />
                        </Table.Header>
                        <Table.Body>
                            {filteredContributors.map((email) => (
                                <Table.Row key={email} data-testid={email}>
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
        margin: 0 0 20px 0;
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
  flex: 1;
  margin-right: 10px;
`;

export {ContributorList};
