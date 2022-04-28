import getStepConfirmationLabel from '@src/connect/components/AppWizard/getStepConfirmationLabel';

const scenarios: {requires_explicit_approval: boolean; isFirst: boolean; isLast: boolean; result: string}[] = [
    {
        requires_explicit_approval: true,
        isFirst: true,
        isLast: true,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.confirm',
    },
    {
        requires_explicit_approval: false,
        isFirst: true,
        isLast: true,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.confirm',
    },
    {
        requires_explicit_approval: true,
        isFirst: true,
        isLast: false,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next',
    },
    {
        requires_explicit_approval: false,
        isFirst: true,
        isLast: false,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.next',
    },
    {
        requires_explicit_approval: true,
        isFirst: false,
        isLast: true,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.confirm',
    },
    {
        requires_explicit_approval: false,
        isFirst: false,
        isLast: true,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.confirm',
    },
    {
        requires_explicit_approval: true,
        isFirst: false,
        isLast: false,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next',
    },
    {
        requires_explicit_approval: false,
        isFirst: false,
        isLast: false,
        result: 'akeneo_connectivity.connection.connect.apps.wizard.action.next',
    },
];

test.each(scenarios)('It returns $result', ({requires_explicit_approval, isFirst, isLast, result}) => {
    expect(
        getStepConfirmationLabel(
            {
                name: 'authorizations',
                requires_explicit_approval,
            },
            isFirst,
            isLast
        )
    ).toBe(result);
});
