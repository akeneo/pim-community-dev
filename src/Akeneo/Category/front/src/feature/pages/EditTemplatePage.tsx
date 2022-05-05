import React, {FC, useCallback, useEffect, useState} from 'react';
import {useParams} from "react-router";
import {LabelCollection, useRouter} from "@akeneo-pim-community/shared";
import {Button, uuid} from "akeneo-design-system";
import {configFrontToBack} from "@akeneo-pim-community/config/lib/models/ConfigServicePayload";

type Params = {
  templateId: string;
  treeId: string;
}
type Attribute = {
  identifier: string;
  code: string;
  labels: LabelCollection;
  order: number;
  isRequired: boolean;
  valuePerChannel: boolean;
  valuePerLocale: boolean;
}
type TextAttribute = Attribute & {
  maxLength: number;
  isTextarea: boolean;
  validationRule: 'none'|'url'|'email'|'regex',
  isRichTextEditor: boolean;
}

type Template = {
  identifier: string;
  code: string;
  labels: LabelCollection;
  attributes: Array<keyof Attribute>;
  category_tree_identifier: string;
}

const EditTemplatePage: FC = () => {
  let {templateId, treeId} = useParams<Params>();
  const router = useRouter();
  const [template, setTemplate] = useState<Template|null>(null);

  const loadTemplate = useCallback(async () => {
    const response = await fetch(router.generate('pim_enrich_category_rest_template_get', {
      identifier: templateId,
      category_tree_identifier: treeId
    }));
    const data = await response.json();

    setTemplate(data);
  }, [templateId, treeId]);

  const addAttribute = useCallback((
    code:string,
    label:string,
    valuePerChannel: boolean,
    valuePerLocale: boolean,
    additionalProperties: object
  ) => {
    const attribute = {
      identifier: `${templateId}_${code}_${uuid()}`,
      code,
      labels: {
        'en_US': label,
      } as LabelCollection,
      valuePerChannel,
      valuePerLocale,
      ...additionalProperties
    } as Attribute;

    setTemplate((data) => {
      if (!data) {
        return null;
      }

      return {
        ...data,
        attributes:[
          ...data.attributes,
          attribute
        ]
      } as Template;
    });
  }, []);

  const saveTemplate = useCallback(async () => {
    const response = await fetch(router.generate('pim_enrich_category_rest_template_edit'), {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(template),
    });
    console.log(response);
  }, [template]);

  useEffect(() => {
    loadTemplate();
  }, [loadTemplate]);

  return (
    <>
      <div>
        <p>Edit template page {templateId} for the category tree {treeId}</p>
      </div>
      <div>
        <Button onClick={() => {
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
      </div>
      <div>
        <pre>{JSON.stringify(template)}</pre>
      </div>
      <Button
        level={"secondary"}
        onClick={saveTemplate}
        >Save</Button>
    </>
  );
};
export {EditTemplatePage}
