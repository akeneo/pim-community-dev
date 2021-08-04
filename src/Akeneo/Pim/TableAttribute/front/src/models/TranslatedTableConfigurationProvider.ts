import {TEMPLATES, TemplateVariation} from './Template';
import {SelectOption, TableConfiguration} from './TableConfiguration';
import {LocaleCode} from '../../../../../AssetManager/front/domain/model/locale';

const getTranslatedTableConfigurationFromVariationTemplate = async (
  variationTemplate: string,
  localeCodes: LocaleCode[]
): Promise<TableConfiguration> => {
  const templateVariation = ([] as TemplateVariation[])
    .concat(...TEMPLATES.map(template => template.template_variations))
    .find(template => template.code === variationTemplate);

  if (!templateVariation) {
    return [];
  }

  const messages = await getMessages(localeCodes);

  const tableConfiguration = templateVariation.tableConfiguration;
  return tableConfiguration.map(columnDefinition => {
    localeCodes.forEach(localeCode => {
      const key = `jsmessages:table_attribute_template.${templateVariation.code}.${columnDefinition.code}.label`;
      const label = messages[localeCode][key];
      if (label) {
        columnDefinition.labels[localeCode] = label;
      }
    });

    if (typeof columnDefinition['options'] !== 'undefined') {
      columnDefinition['options'] = columnDefinition['options'].map((option: SelectOption) => {
        localeCodes.forEach(localeCode => {
          const key = `jsmessages:table_attribute_template.${templateVariation.code}.${columnDefinition.code}.options.${option.code}`;
          const label = messages[localeCode][key];
          if (label) {
            option.labels[localeCode] = label;
          }
        });

        return option;
      });
    }

    return columnDefinition;
  });
};

const getMessages = async (localeCodes: LocaleCode[]) => {
  const responses = {};
  for await (const localeCode of localeCodes) {
    const response = await fetch(`js/translation/${localeCode}.js`);
    const json = await response.json();
    responses[localeCode] = json.messages;
  }
  return responses;
};

export {getTranslatedTableConfigurationFromVariationTemplate};
