import {useFeatureFlags} from '@akeneo-pim-community/shared';
import {Field, Helper, TextInput} from 'akeneo-design-system';
import {useQueryClient} from 'react-query';
import {useSaveStatus} from '../../hooks/useSaveStatus';
import {useUpdateTemplateAttribute} from '../../hooks/useUpdateTemplateAttribute';
import {Attribute} from '../../models';
import {useDebounceCallback} from '../../tools/useDebounceCallback';
import {Status} from '../providers/SaveStatusProvider';
import {useTemplateForm} from '../providers/TemplateFormProvider';

const useTranslationFormData = (attribute: Attribute, localeCode: string) => {
  const [state] = useTemplateForm();

  const translationFormData = state.attributes?.[attribute.uuid]?.[localeCode];

  if (undefined === translationFormData) {
    return {
      value: attribute.labels[localeCode] || '',
      errors: [],
    };
  }

  return translationFormData;
};

type Props = {
  attribute: Attribute;
  localeCode: string;
  label: string;
};

export const LabelTranslationInput = ({attribute, localeCode, label}: Props) => {
  const featureFlag = useFeatureFlags();
  const queryClient = useQueryClient();

  const {handleStatusListChange} = useSaveStatus();
  const saveStatusId = attribute.uuid + '_label_translation_' + localeCode;

  const [, dispatch] = useTemplateForm();

  const mutation = useUpdateTemplateAttribute(attribute.template_uuid, attribute.uuid);
  const debouncedUpdateAttributeLabel = useDebounceCallback((value: string) => {
    handleStatusListChange(saveStatusId, Status.SAVING);
    mutation.mutate(
      {labels: {[localeCode]: value}},
      {
        onSuccess: async () => {
          handleStatusListChange(saveStatusId, Status.SAVED);
          await queryClient.invalidateQueries(['get-template', attribute.template_uuid]);
          dispatch({
            type: 'attribute_label_translation_saved',
            payload: {attributeUuid: attribute.uuid, localeCode, value},
          });
        },
        onError: error => {
          handleStatusListChange(saveStatusId, Status.ERRORS);
          const errors = error.data.map(({error}) => error.message);
          dispatch({
            type: 'save_attribute_label_translation_failed',
            payload: {attributeUuid: attribute.uuid, localeCode, errors},
          });
        },
      }
    );
  }, 300);

  const handleTranslationChange = async (value: string) => {
    handleStatusListChange(saveStatusId, Status.EDITING);
    dispatch({
      type: 'attribute_label_translation_changed',
      payload: {attributeUuid: attribute.uuid, localeCode, value},
    });
    debouncedUpdateAttributeLabel(value);
  };

  const translationFormData = useTranslationFormData(attribute, localeCode);

  return (
    <Field label={label} locale={localeCode}>
      <TextInput
        onChange={handleTranslationChange}
        invalid={false}
        value={translationFormData.value}
        readOnly={!featureFlag.isEnabled('category_update_template_attribute')}
      />
      {translationFormData.errors.map(message => (
        <Helper level="error" key={message}>
          {message}
        </Helper>
      ))}
    </Field>
  );
};
