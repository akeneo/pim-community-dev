import {createRecord as recordFactory} from 'akeneoenrichedentity/domain/model/record/record';
import EntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import recordSaver from 'akeneoenrichedentity/infrastructure/saver/record';
import {recordCreationSucceeded, recordCreationErrorOccured} from 'akeneoenrichedentity/domain/event/record/create';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';

export const createRecord = (recordCode: string, entityCode: string, labels: {[localeCode: string]: string}) => async (dispatch: any): Promise<void> => {
    try {
        const record = recordFactory(
            RecordIdentifier.create(recordCode),
            EntityIdentifier.create(entityCode),
            LabelCollection.create(labels)
        );
        let errors = await recordSaver.create(record);

        if (errors) {
            const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
            dispatch(recordCreationErrorOccured(validationErrors));

            return;
        }
    } catch (error) {
        dispatch(recordCreationErrorOccured(error));

        return;
    }

    dispatch(recordCreationSucceeded());

    return;
};
