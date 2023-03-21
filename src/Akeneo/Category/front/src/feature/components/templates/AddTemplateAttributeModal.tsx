import {NotificationLevel, translate, useNotify} from '@akeneo-pim-community/shared';
import {useState} from "react";
import {useQueryClient} from "react-query";
import {useCreateAttribute} from "../../hooks/useCreateAttribute";
import {userContext} from "@akeneo-pim-community/shared/lib/dependencies/user-context";
import {
    AttributesIllustration,
    Button,
    Checkbox,
    Field,
    Helper,
    Link,
    Modal,
    SelectInput,
    TextInput
} from "akeneo-design-system";
import styled from "styled-components";

const Content = styled.div`
  padding-bottom: 20px;
`;
const FieldSet = styled.div`
  & > * {
    margin-top: 20px;
  }
`;

const HelperField = styled(Helper)`
  width: 100%;
`;

type Form = {
    label: string;
    code: string;
    type: string;
    isScopable: boolean;
    isLocalizable: boolean;
};
type FormError = { label: string[]; code: string[]; type: string[]; isScopable: string[]; isLocalizable: string[]};

type Props = {
    templateId: string;
    onClose: () => void;
}

export const AddTemplateAttributeModal = ({templateId, onClose}: Props) => {
    const catalogLocale = userContext.get('catalogLocale');
    const mutation = useCreateAttribute();
    const notify = useNotify();
    const queryClient = useQueryClient();
    const [form, setForm] = useState<Form>({ label: "", code: "", type: "text", isScopable: false, isLocalizable: false });
    const [error, setError] = useState<FormError>({
        label: [],
        code: [],
        type: [],
        isScopable: [],
        isLocalizable: [],
    });

    const attributeTypes = [
        {
            type: "text",
            label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.text')
        },
        {
            type: "textarea",
            label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.textarea')
        },
        {
            type: "richtext",
            label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.richtext')
        },
        {
            type: "image",
            label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.image')
        },
    ];

    const displayError = (errorMessages: string[]) => {
        return errorMessages.map((message) => {
            return (
                <Helper level="error">
                    {message}
                </Helper>
            )
        });
    };

    const handleCreate = () => {
        mutation.mutate(
            {
                templateId,
                code: form.code,
                locale: catalogLocale,
                label: form.label,
                type: form.type,
                isScopable: form.isScopable,
                isLocalizable: form.isLocalizable
            },
            {
                onError: (error) => {
                    setError({
                        code: error.data?.code || [],
                        label: error.data?.label || [],
                        type: error.data?.type || [],
                        isScopable: error.data?.isScopable || [],
                        isLocalizable: error.data?.isLocalizable || [],
                    });
                },
                onSuccess: async () => {
                    await queryClient.invalidateQueries(['template', templateId]);
                    notify(NotificationLevel.SUCCESS, translate('akeneo.category.template.add_attribute.success.notification'));
                    onClose();
                },
            }
        );
    };

    return (
        <Modal illustration={<AttributesIllustration/>} onClose={onClose} closeTitle={translate('pim_common.close')}>
            <Modal.SectionTitle color="brand">
                {translate('akeneo.category.template.add_attribute.confirmation_modal.section_title')}
            </Modal.SectionTitle>
            <Modal.Title>{translate('akeneo.category.template.add_attribute.confirmation_modal.title')}</Modal.Title>
            <Content>
                <FieldSet>
                    <HelperField level="info">
                        {translate('akeneo.category.template.add_attribute.confirmation_modal.head_helper')}
                        <Link href="#">
                            {translate('akeneo.category.template.add_attribute.confirmation_modal.link')}
                        </Link>
                    </HelperField>
                    <Field label={translate('pim_common.label')} locale={catalogLocale}>
                        <TextInput
                            value={form.label}
                            invalid={error.label.length > 0}
                            onChange={(label: string) => {
                                setForm({...form, label: label});
                            }}
                        />
                        {error.label.length >= 1 && displayError(error.label)}
                    </Field>
                    <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
                        <TextInput
                            value={form.code}
                            invalid={error.code.length > 0}
                            onChange={(code: string) => {
                                setForm({...form, code: code});
                            }}
                        />
                        {error.code.length >= 1 && displayError(error.code)}
                    </Field>
                    <Field label={translate("akeneo.category.template.add_attribute.confirmation_modal.input.type.label")}>
                        <SelectInput
                            emptyResultLabel={translate("akeneo.category.template.add_attribute.confirmation_modal.input.type.empty")}
                            openLabel={''}
                            value={form.type}
                            invalid={error.type.length > 0}
                            onChange={(type: string) => {
                                setForm({...form, type: type});
                            }}
                         >
                            {attributeTypes.map((attribute: {type: string, label: string}) => {
                                return (
                                    <SelectInput.Option
                                        title={attribute.label}
                                        value={attribute.type}
                                    >{attribute.label}</SelectInput.Option>
                                );
                            })}
                        </SelectInput>
                        {error.type.length >= 1 && displayError(error.type)}
                    </Field>
                    <Field label={""}>
                        <Checkbox
                            checked={form.isScopable}
                            onChange={(value: boolean) => {
                                setForm({...form, isScopable: value});
                            }}
                        >{translate("pim_enrich.entity.attribute.property.scopable")}</Checkbox>
                        {error.isScopable.length >= 1 && displayError(error.isScopable)}
                    </Field>
                    <Field label={""}>
                        <Checkbox
                            checked={form.isLocalizable}
                            onChange={(value: boolean) => {
                                setForm({...form, isLocalizable: value});
                            }}
                        >{translate("pim_enrich.entity.attribute.property.localizable")}</Checkbox>
                        {error.isLocalizable.length >= 1 && displayError(error.isLocalizable)}
                    </Field>
                    <HelperField level="info" inline>
                        {translate('akeneo.category.template.add_attribute.confirmation_modal.tail_helper')}
                    </HelperField>
                </FieldSet>
            </Content>
            <Modal.BottomButtons>
                <Button level="tertiary" onClick={onClose}>
                    {translate('pim_common.cancel')}
                </Button>
                <Button disabled={mutation.isLoading} level="primary" onClick={handleCreate}>
                    {translate('akeneo.category.template.add_attribute.confirmation_modal.create')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
}
