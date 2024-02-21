import {Step} from './WizardModal';

const getStepConfirmationLabel = (step: Step, _isFirst: boolean, isLast: boolean): string => {
    if (isLast) {
        return 'akeneo_connectivity.connection.connect.apps.wizard.action.confirm';
    }

    if (step.requires_explicit_approval) {
        return 'akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next';
    }

    return 'akeneo_connectivity.connection.connect.apps.wizard.action.next';
};

export default getStepConfirmationLabel;
