import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {httpGet} from "./fetch";
import {Attribute} from "../models/Attribute";

export const getByIdentifier = async (attributeIdentifier: string, router: Router): Promise<Attribute | null> => {
  const url = router.generate('pim_enrich_attribute_rest_get', { identifier: attributeIdentifier });
  const response = await httpGet(url);

  if (response.status === 404) {
    return null;
  }

  return await response.json();
};
