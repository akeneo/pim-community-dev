import {translate, useTranslate} from '@akeneo-pim-community/shared';
import {useState} from "react";
import {useCreateAttribute} from "../../hooks/useCreateAttribute";
import {userContext} from "@akeneo-pim-community/shared/lib/dependencies/user-context";
import {TextInput} from "akeneo-design-system/lib/components/Input/TextInput/TextInput";
import {AttributesIllustration, Button, Field, Helper, Link, Modal} from "akeneo-design-system";
import styled from "styled-components";
import {useHistory} from 'react-router';

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
    template_id: string;
    onClose: () => void;
}

export const AddTemplateAttributeModal = ({template_id, onClose}: Props) => {
    const catalogLocale = userContext.get('catalogLocale');
    const history = useHistory();
    const mutation = useCreateAttribute();
    const [form, setForm] = useState<Form>({ label: "", code: "" });

    const [error, setError] = useState<FormError>({
        label: [],
        code: []
    });

    const handleCreate = () => {
        mutation.mutate({
            code: form.code,
            locale: catalogLocale,
            label: form.label,
            type: "text",
            is_localizable: true,
            is_scopable: true
        });

        history.push('/'); // TODO redirect to Category edit page
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
                            onChange={(label) => {
                                setForm({ ...form, label: label });
                            }}
                        />
                    </Field>
                    <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
                        <TextInput
                            value={form.code}
                            onChange={(code) => {
                                setForm({ ...form, code: code });
                            }}
                        />
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
