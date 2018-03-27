import {Attribute, AttributeInterface} from 'pimfront/product-grid/domain/model/field';

export default (backendAttribute: any): AttributeInterface => {
  const frontendAttribute = {...backendAttribute, identifier: backendAttribute.code};

  return Attribute.createFromAttribute(frontendAttribute);
};
