import {Field, Helper, TextInput} from 'akeneo-design-system';
import {useQueryClient} from 'react-query';
import {useSaveStatus} from '../../../hooks/useSaveStatus';
import {useUpdateTemplateAttribute} from '../../../hooks/useUpdateTemplateAttribute';
import {Attribute} from '../../../models';
import {useDebounceCallback} from '../../../tools/useDebounceCallback';
import {Status} from '../../providers/SaveStatusProvider';
import {useTemplateForm} from '../../providers/TemplateFormProvider';

const useFormData = (attribute: Attribute, localeCode: string) => {
  const [state] = useTemplateForm();

  const formData = state.attributes?.[attribute.uuid]?.labels?.[localeCode];

  if (undefined === formData) {
    return {
      value: attribute.labels[localeCode] || '',
      errors: [],
    };
  }

  return formData;
};

type Props = {
  attribute: Attribute;
  localeCode: string;
  label: string;
};

export const AttributeLabelTranslationInput = ({attribute, localeCode, label}: Props) => {
  const queryClient = useQueryClient();

  const {handleStatusListChange} = useSaveStatus();
  const saveStatusId = attribute.uuid + '_label_translation_' + localeCode;

  const [, dispatch] = useTemplateForm();

  const mutation = useUpdateTemplateAttribute(attribute.template_uuid, attribute.uuid);
  const debouncedUpdateAttributeLabel = useDebounceCallback(async (value: string) => {
    handleStatusListChange(saveStatusId, Status.SAVING);
    await mutation.mutateAsync(
      {labels: {[localeCode]: value}},
      {
        onError: error => {
          const errors = error.data.map(({error}) => error.message);
          dispatch({
            type: 'save_attribute_label_translation_failed',
            payload: {attributeUuid: attribute.uuid, localeCode, errors},
          });
          handleStatusListChange(saveStatusId, Status.ERRORS);
        },
      }
    );
    await queryClient.invalidateQueries(['get-template', attribute.template_uuid]);
    dispatch({
      type: 'attribute_label_translation_saved',
      payload: {attributeUuid: attribute.uuid, localeCode, value},
    });
    handleStatusListChange(saveStatusId, Status.SAVED);
  }, 300);

  const handleTranslationChange = async (value: string) => {
    dispatch({
      type: 'attribute_label_translation_changed',
      payload: {attributeUuid: attribute.uuid, localeCode, value},
    });
    debouncedUpdateAttributeLabel(value);
    handleStatusListChange(saveStatusId, Status.EDITING);
  };

  const formData = useFormData(attribute, localeCode);

  return (
    <Field label={label} locale={localeCode}>
      <TextInput onChange={handleTranslationChange} invalid={false} value={formData.value} />
      {formData.errors.map(message => (
        <Helper level="error" key={message}>
          {message}
        </Helper>
      ))}
    </Field>
  );
};
