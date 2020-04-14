import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {httpGet} from "./fetch";
import {Channel} from "../models/Channel";

export const getAll = async (router: Router): Promise<Channel[]> => {
  // TODO Cache the result to avoid multiple calls
  const url = router.generate('pim_enrich_channel_rest_index');
  const response = await httpGet(url);
  return await response.json();
};
