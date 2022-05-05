import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ContributorList} from './ContributorList';
import userEvent from '@testing-library/user-event';
import {ContributorEmail} from '../../models';

const contributors = ['contributor1@example.com', 'contributor2@example.com', 'another@example.com'];

test('it renders an empty list', () => {
    renderWithProviders(<ContributorList contributors={[]} setContributors={() => {}} />);
    expect(screen.getByText('onboarder.supplier.supplier_edit.contributors_form.no_contributor')).toBeInTheDocument();
});

test('it renders a searchable list of contributors', () => {
    renderWithProviders(<ContributorList contributors={contributors} setContributors={() => {}} />);
    expect(
        screen.queryByText('onboarder.supplier.supplier_edit.contributors_form.no_contributor')
    ).not.toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('onboarder.supplier.supplier_edit.contributors_form.search_by_email_address')
    ).toBeInTheDocument();
    expect(screen.getByText('onboarder.supplier.supplier_edit.contributors_form.result_counter')).toBeInTheDocument();

    contributors.map(contributorEmail => expect(screen.getByText(contributorEmail)).toBeInTheDocument());
});

test('it allows new contributors to be added without duplicates', () => {
    let defaultContributors: ContributorEmail[] = [];
    const setContributors = (newContributors: ContributorEmail[]) => {
        defaultContributors = newContributors;
    };
    renderWithProviders(<ContributorList contributors={defaultContributors} setContributors={setContributors} />);

    userEvent.type(screen.getByTestId('tag-input'), contributors.join('{space}').concat('{space}'));
    userEvent.type(screen.getByTestId('tag-input'), 'contributor1@example.com{space}');
    userEvent.click(screen.getByText('onboarder.supplier.supplier_edit.contributors_form.add_button'));

    renderWithProviders(<ContributorList contributors={defaultContributors} setContributors={setContributors} />);
    contributors.map(contributorEmail => expect(screen.queryAllByText(contributorEmail)).toHaveLength(1));
});

test('it excludes invalid emails', () => {
    let defaultContributors: ContributorEmail[] = [];
    const setContributors = (newContributors: ContributorEmail[]) => {
        defaultContributors = newContributors;
    };
    renderWithProviders(<ContributorList contributors={defaultContributors} setContributors={setContributors} />);

    const tooLongEmail = 'tooLongEmail'.repeat(50).concat('@domain.com');
    const invalidContributorEmails = ['invalidemail', tooLongEmail];
    const validEmail = 'validEmail@domain.com';
    userEvent.type(
        screen.getByTestId('tag-input'),
        invalidContributorEmails.join('{space}').concat('{space}').concat(validEmail).concat('{space}')
    );
    userEvent.click(screen.getByText('onboarder.supplier.supplier_edit.contributors_form.add_button'));

    renderWithProviders(<ContributorList contributors={defaultContributors} setContributors={setContributors} />);
    expect(screen.getByText(validEmail)).toBeInTheDocument();
    invalidContributorEmails.map(invalidContributorEmail =>
        expect(screen.queryByText(invalidContributorEmail)).not.toBeInTheDocument()
    );
});

test('it displays a warning helper if a contributor email is not valid', () => {
    let defaultContributors: ContributorEmail[] = [];
    const setContributors = (newContributors: ContributorEmail[]) => {
        defaultContributors = newContributors;
    };
    renderWithProviders(<ContributorList contributors={defaultContributors} setContributors={setContributors} />);

    userEvent.type(screen.getByTestId('tag-input'), 'invalidemail'.concat('{space}'));

    expect(
        screen.getByText('onboarder.supplier.supplier_edit.contributors_form.invalid_emails_warning')
    ).toBeInTheDocument();
});
