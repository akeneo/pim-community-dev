const BaseVariantNavigation = require('pim/product-edit-form/variant-navigation');
const FetcherRegistry = require('pim/fetcher-registry');

class VariantNavigation extends BaseVariantNavigation {
  /**
   * Tests the identifier group permission and the creation ACL depending on the entity type the user wants to
   * create.
   */
  async isCreationGranted(isVariantProduct: boolean): Promise<boolean> {
    if (false === (await super.isCreationGranted(isVariantProduct))) {
      return false;
    }

    if (!isVariantProduct) {
      return true;
    }

    const permissions = await FetcherRegistry.getFetcher('permission').fetchAll();
    const identifierAttributes = await FetcherRegistry.getFetcher('attribute').getIdentifierAttribute();

    const attributeGroupPermissions: {edit: boolean} = permissions.attribute_groups.find(
      ({code}: {code: string}) => code === identifierAttributes.group
    );

    return undefined !== attributeGroupPermissions && true === attributeGroupPermissions.edit;
  }
}

export = VariantNavigation;
