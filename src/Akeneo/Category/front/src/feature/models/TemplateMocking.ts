/**
 * TemplateMocking : will be removed when templates can be GET and exploited
 * Here are the definition of the static template for the MDP
 */

import {
  CategoryAttributeDefinition,
  CATEGORY_ATTRIBUTE_TYPE_IMAGE,
  CATEGORY_ATTRIBUTE_TYPE_TEXT,
} from './Attribute';
import {CategoryAttributes} from './Category';
import {buildCompositeKey} from './CompositeKey';

// mocked attributes
// TODO use attribute coming from GET template via props
export const attributeDefinitions: {[attributeCode: string]: CategoryAttributeDefinition} = {
  description: {
    uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
    code: 'description',
    type: CATEGORY_ATTRIBUTE_TYPE_TEXT,
  },
  banner: {
    uuid: '8dda490c-0fd1-4485-bdc5-342929783d9a',
    code: 'banner_image',
    type: CATEGORY_ATTRIBUTE_TYPE_IMAGE,
  },
  seo_meta_title: {
    uuid: '4873080d-32a3-42a7-ae5c-1be518e40f3d',
    code: 'seo_meta_title',
    type: CATEGORY_ATTRIBUTE_TYPE_TEXT,
  },
  seo_meta_description: {
    uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
    code: 'seo_meta_description',
    type: CATEGORY_ATTRIBUTE_TYPE_TEXT,
  },
  seo_keywords: {
    uuid: '4ba33f06-de92-4366-8322-991d1bad07b9',
    code: 'seo_keywords',
    type: CATEGORY_ATTRIBUTE_TYPE_TEXT,
  },
};

export const defaultAttributeValues: CategoryAttributes = {
  [buildCompositeKey(attributeDefinitions.description, 'en_US')]: {
    data: 'Desc EN',
    locale: 'en_US',
    attribute_code: buildCompositeKey(attributeDefinitions.description),
  },
  [buildCompositeKey(attributeDefinitions.description, 'fr_FR')]: {
    data: 'Desc EN',
    locale: 'fr_FR',
    attribute_code: buildCompositeKey(attributeDefinitions.description),
  },
  [buildCompositeKey(attributeDefinitions.banner)]: {
    data: {
      size: 1,
      file_path: '',
      mime_type: '',
      extension: '',
      original_filename: '',
    },
    locale: null,
    attribute_code: buildCompositeKey(attributeDefinitions.banner),
  },
  [buildCompositeKey(attributeDefinitions.seo_meta_title, 'en_US')]: {
    data: 'seo meta title EN',
    locale: 'en_US',
    attribute_code: buildCompositeKey(attributeDefinitions.seo_meta_title),
  },
  [buildCompositeKey(attributeDefinitions.seo_meta_description, 'en_US')]: {
    data: 'seo meta desc EN',
    locale: 'en_US',
    attribute_code: buildCompositeKey(attributeDefinitions.seo_meta_description),
  },
  [buildCompositeKey(attributeDefinitions.seo_keywords, 'en_US')]: {
    data: 'seo keywords EN',
    locale: 'en_US',
    attribute_code: buildCompositeKey(attributeDefinitions.seo_keywords),
  },
};
