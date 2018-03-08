// import {PropertyInterface, RawPropertyInterface} from 'pimfront/product-grid/domain/model/field';

// export default class Boolean implements PropertyInterface {
//   readonly identifier: string;

//   private constructor({identifier}: RawPropertyInterface) {
//     if (undefined === identifier) {
//       throw new Error('Property identifier needs to be defined to create a field');
//     }

//     this.identifier = identifier;
//   }

//   public static createFromProperty(property: RawPropertyInterface): Boolean {
//     return new Boolean(property);
//   }

//   public getLabel(locale: string): string {
//     return this.identifier;
//   }
// }
