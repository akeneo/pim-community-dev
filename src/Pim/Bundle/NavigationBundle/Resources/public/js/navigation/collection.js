/* global define */
import Backbone from 'backbone';
import NavigationModel from 'oro/navigation/model';


/**
 * @export  oro/navigation/collection
 * @class   oro.navigation.Collection
 * @extends Backbone.Collection
 */
export default Backbone.Collection.extend({
  model: NavigationModel
});

