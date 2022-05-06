import React, {FC, useCallback, useEffect, useState} from 'react';
import {useParams} from "react-router";
import {
  getLabel,
  LabelCollection,
  NotificationLevel,
  PageContent,
  PageHeader,
  Section,
  useNotify,
  useRouter
} from "@akeneo-pim-community/shared";
import {
  AttributeTextareaIcon,
  AttributeTextIcon,
  Breadcrumb,
  Button,
  Field,
  SectionTitle,
  TextAreaInput,
  TextInput
} from "akeneo-design-system";

type Params = {
  templateId: string;
  treeId: string;
}
type Attribute = {
  identifier: string;
  templateIdentifier: string;
  code: string;
  type: 'text';
  labels: LabelCollection;
  order: number;
  isRequired: boolean;
  valuePerChannel: boolean;
  valuePerLocale: boolean;
}
type TextAttribute = Attribute & {
  maxLength: number;
  isTextarea: boolean;
  validationRule: 'none' | 'url' | 'email' | 'regex',
  isRichTextEditor: boolean;
}

type Template = {
  identifier: string;
  code: string;
  labels: LabelCollection;
  attributes: TextAttribute[];
  categoryTreeIdentifier: string;
}

const toCamel = (value: string): string => {
  return value.replace(/([-_][a-z])/ig, (letter: string) => {
    return letter.toUpperCase()
    .replace('-', '')
    .replace('_', '');
  });
};

const toSnake = (value: string): string => {
  return value.replace(/[A-Z]/g, (letter: string) => {
    return `_${letter.toLowerCase()}`
  });
};

const keysToCamel = (value: any): any => {
  if (Array.isArray(value)) {
    return value.map((i) => {
      return keysToCamel(i);
    });
  }

  if (value instanceof Object) {
    const n = {};

    Object.keys(value)
    .forEach((k) => {
      n[toCamel(k)] = (k !== 'labels') ? keysToCamel(value[k]) : value[k];
    });

    return n;
  }

  return value;
};

const keysToSnake = (value: any): any => {
  if (Array.isArray(value)) {
    return value.map((i) => {
      return keysToSnake(i);
    });
  }

  if (value instanceof Object) {
    const n = {};

    Object.keys(value)
    .forEach((k) => {
      n[toSnake(k)] = (k !== 'labels') ? keysToSnake(value[k]) : value[k];
    });

    return n;
  }

  return value;

}

const EditTemplatePage: FC = () => {
  let {templateId, treeId} = useParams<Params>();
  const router = useRouter();
  const [template, setTemplate] = useState<Template | null>(null);
  const [updatedTemplate, setUpdateTemplate] = useState<Template | null>(null);
  const notify = useNotify();

  const loadTemplate = useCallback(async () => {
    const response = await fetch(router.generate('pim_enrich_category_rest_template_get', {
      identifier: templateId,
      categoryTreeIdentifier: treeId
    }));
    const data = await response.json();

    setTemplate(data);
  }, [templateId, treeId]);

  const addAttribute = useCallback(async (
    code: string,
    label: string,
    valuePerChannel: boolean,
    valuePerLocale: boolean,
    additionalProperties: object
  ) => {
    const attribute = {
      templateIdentifier: templateId,
      code,
      type: 'text',
      labels: {
        'en_US': label,
      } as LabelCollection,
      valuePerChannel,
      valuePerLocale,
      ...additionalProperties
    };

    // example of saving the attribute when it is created
    const response = await fetch(router.generate('pim_enrich_category_rest_template_attribute_create', {
      identifier: templateId,
    }), {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(keysToSnake(attribute)),
    });

    const createdAttribute = keysToCamel(await response.json());
    if (!response.ok) {
      console.error('En error occurred during the creation of the attribute', createdAttribute);
      notify(NotificationLevel.ERROR, 'En error occurred during the creation of the attribute');
      return;
    }

    setTemplate((data) => {
      if (!data) {
        return null;
      }

      return {
        ...data,
        attributes: [
          ...data.attributes,
          createdAttribute
        ]
      } as Template;
    });
    notify(NotificationLevel.SUCCESS, 'Attribute added with success');
  }, []);

  const saveTemplate = useCallback(async () => {
    if (template === null) {
      return;
    }

    const response = await fetch(router.generate('pim_enrich_category_rest_template_edit'), {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(keysToSnake(template)),
    });
    const data = await response.json() as Template;

    setUpdateTemplate(data);
  }, [template]);

  useEffect(() => {
    loadTemplate();
  }, [loadTemplate]);

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <PageHeader.Breadcrumb>
            <Breadcrumb>
              <Breadcrumb.Step>Settings</Breadcrumb.Step>
              <Breadcrumb.Step>Categories</Breadcrumb.Step>
              <Breadcrumb.Step>Master</Breadcrumb.Step>
              <Breadcrumb.Step>Template Default</Breadcrumb.Step>
            </Breadcrumb>
          </PageHeader.Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.Title>
          Edit template page {templateId}
        </PageHeader.Title>
        <PageHeader.Content>
         Category tree {treeId}
        </PageHeader.Content>
        <PageHeader.UserActions>
          <Button size="small" onClick={() => {
            addAttribute(
              'a_text_attribute',
              'A text attribute',
              true,
              false,
              {
                maxLength: 255,
                isTextarea: false,
                validationRule: 'none',
                isRichTextEditor: false
              },
            );
          }}>Add text attribute</Button>

          <Button size="small"
            level={"secondary"}
            onClick={saveTemplate}
          >Save</Button>
        </PageHeader.UserActions>

      </PageHeader>
      <PageContent>
        {(template !== null) && (
          <Section>
            <SectionTitle title="Properties">
              <SectionTitle.Title>Properties</SectionTitle.Title>
            </SectionTitle>
            <Field label="Code">
              <TextInput
                value={template.code}
                readOnly={true}
              />
            </Field>
            <Field label="Label" locale="en_US">
              <TextInput
                value={getLabel(template.labels, 'en_US', template.code)}
                readOnly={true}
              />
            </Field>

            <SectionTitle title="Attributes">
              <SectionTitle.Title>Attributes</SectionTitle.Title>
            </SectionTitle>
            {template.attributes.map((attribute) => (
              <>
                {attribute.type === 'text' && !attribute.isTextarea && (
                  <Field label={attribute.code}>
                    <AttributeTextIcon/>
                    <TextInput
                      value={getLabel(attribute.labels, 'en_US', attribute.code)}
                      readOnly={true}
                    />
                  </Field>
                )}
                {attribute.type === 'text' && attribute.isTextarea && (
                  <Field label="Code">
                    <AttributeTextareaIcon/>
                    <TextAreaInput
                      value={getLabel(attribute.labels, 'en_US', attribute.code)}
                      readOnly={true}
                    />
                  </Field>
                )}
              </>
            ))}
          </Section>
        )}
        <Section>
          <hr/>
          <p>Update template</p>
          <pre>{JSON.stringify(updatedTemplate)}</pre>
        </Section>
      </PageContent>
    </>
  );
};
export {EditTemplatePage}
