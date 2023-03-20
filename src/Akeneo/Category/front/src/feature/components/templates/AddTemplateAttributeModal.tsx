import {NotificationLevel, translate, useNotify} from '@akeneo-pim-community/shared';
import {useState} from "react";
import {useQueryClient} from "react-query";
import {useCreateAttribute} from "../../hooks/useCreateAttribute";
import {userContext} from "@akeneo-pim-community/shared/lib/dependencies/user-context";
import {AttributesIllustration, Button, Field, Helper, Link, Modal, TextInput} from "akeneo-design-system";
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

type Form = { label: string; code: string };
type FormError = { label: string[]; code: string[] };

type Props = {
    templateId: string;
    onClose: () => void;
}

export const AddTemplateAttributeModal = ({templateId, onClose}: Props) => {
    const catalogLocale = userContext.get('catalogLocale');
    const mutation = useCreateAttribute();
    const notify = useNotify();
    const queryClient = useQueryClient();
    const [form, setForm] = useState<Form>({ label: "", code: "" });
    const [error, setError] = useState<FormError>({
        label: [],
        code: []
    });

    const handleCreate = () => {
        mutation.mutate(
            {
                templateId,
                code: form.code,
                locale: catalogLocale,
                label: form.label,
                type: "text",
                isLocalizable: true,
                isScopable: true
            },
            {
                onError: (error) => {
                    setError({
                        code: error.data?.code || [],
                        label: error.data?.label || []
                    });
                },
                onSuccess: async () => {
                    // TODO the invalidation seems to not work: last created attribute is not visible until page refresh
                    await queryClient.invalidateQueries(['template', templateId]);
                    notify(NotificationLevel.SUCCESS, translate('akeneo.category.template.add_attribute.success.notification'));
                    onClose();
                },
            }
        );
    };

    return (
        <Modal illustration={<AttributesIllustration />} onClose={onClose} closeTitle={translate('pim_common.close')}>
            <Modal.SectionTitle color="brand">
                {translate('akeneo.category.template.add_attribute.confirmation_modal.section_title')}
            </Modal.SectionTitle>
            <Modal.Title>{translate('akeneo.category.template.add_attribute.confirmation_modal.title')}</Modal.Title>
            <Content>
                <FieldSet>
                    <HelperField level="info">
                        {translate('akeneo.category.template.add_attribute.confirmation_modal.helper')}
                        <Link href="#">
                            {translate('akeneo.category.template.add_attribute.confirmation_modal.link')}
                        </Link>
                    </HelperField>
                    <Field label={translate('pim_common.label')} locale={catalogLocale}>
                        <TextInput
                            value={form.label}
                            invalid={error.label.length > 0}
                            onChange={(label) => {
                                setForm({ ...form, label: label });
                            }}
                        />
                        {error.label.length > 0 &&
                            error.label.map((message) => {
                                return (
                                    <Helper level="error">
                                        {message}
                                    </Helper>
                                )
                            })
                        }
                    </Field>
                    <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
                        <TextInput
                            value={form.code}
                            invalid={error.code.length > 0}
                            onChange={(code) => {
                                setForm({ ...form, code: code });
                            }}
                        />
                        {error.code.length > 0 &&
                            error.code.map((message) => {
                                return (
                                    <Helper level="error">
                                        {message}
                                    </Helper>
                                )
                            })
                        }
                    </Field>
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
