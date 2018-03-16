import {Product, ProductModel, ModelType} from 'pimfront/product-grid/domain/model/product';

const createBuilder = (defaultMeta, defaultIdentifier, defaultFamily) => {
  return class Builder {
    private meta = defaultMeta;
    private identifier = defaultIdentifier;
    private family = defaultFamily;

    build() {
      return {
        meta: this.meta,
        family: this.family,
        identifier: this.identifier,
      };
    }

    withIdentifier(identifier) {
      this.identifier = identifier;

      return this;
    }

    withFamily(family) {
      this.family = family;

      return this;
    }

    withMeta(key, value) {
      this.meta[key] = value;

      return this;
    }
  };
};

const ProductBuilder = createBuilder(
  {
    label: {en_US: 'My label'},
    image: {filePath: 'asset/img.png', originalFilename: 'toto.png'},
    id: 12,
    completenesses: [],
    model_type: ModelType.Product,
    has_children: false,
  },
  'my_identifier',
  'my_family'
);

const ProductModelBuilder = createBuilder(
  {
    label: {en_US: 'My label'},
    image: {filePath: 'asset/img.png', originalFilename: 'toto.png'},
    id: 12,
    model_type: ModelType.ProductModel,
    has_children: false,
  },
  'my_identifier',
  'my_family'
);

describe('>>>MODEL --- product', () => {
  test('get label with existing locale', () => {
    const rawProduct = new ProductBuilder()
      .withMeta('label', {en_US: 'My label'})
      .withIdentifier('my_awesome_indentifier')
      .build();

    const product = Product.create(rawProduct);

    expect(product.getLabel('ecommerce', 'en_US')).toBe('My label');
  });

  test('get label with existing locale', () => {
    const rawProduct = new ProductBuilder()
      .withMeta('label', {en_US: 'My label'})
      .withIdentifier('my_awesome_indentifier')
      .build();

    const product = Product.create(rawProduct);

    expect(product.getLabel('ecommerce', 'fr_FR')).toBe('my_awesome_indentifier');
  });

  test('get a completeness for the given locale and scope', () => {
    const rawProduct = new ProductBuilder()
      .withMeta('completenesses', [
        {
          locale: 'de_DE',
          channel: 'ecommerce',
          ratio: 60,
          missing: 2,
          required: 5,
        },
        {
          locale: 'en_US',
          channel: 'ecommerce',
          ratio: 60,
          missing: 2,
          required: 5,
        },
      ])
      .build();

    const product = Product.create(rawProduct);

    expect(product.getCompleteness('ecommerce', 'en_US')).toEqual({
      locale: 'en_US',
      channel: 'ecommerce',
      ratio: 60,
      missing: 2,
      required: 5,
    });
    expect(product.getCompleteness('ecommerce', 'fr_FR')).toEqual({});
  });

  test('get an image', () => {
    const rawProductWithImage = new ProductBuilder().withMeta('image', {filePath: '/my/image/is/so/cool.png'}).build();

    const productWithImage = Product.create(rawProductWithImage);

    expect(productWithImage.getImagePath()).toBe('%2Fmy%2Fimage%2Fis%2Fso%2Fcool.png');

    const rawProductWithoutImage = new ProductBuilder()
      .withMeta('image', {filePath: '/my/image/is/so/cool.png'})
      .build();

    const productWithoutImage = Product.create(rawProductWithoutImage);

    expect(productWithoutImage.getImagePath()).toBe('%2Fmy%2Fimage%2Fis%2Fso%2Fcool.png');
  });

  test('get an identifier', () => {
    const rawProduct = new ProductBuilder().withIdentifier('cool_identifier').build();

    const product = Product.create(rawProduct);

    expect(product.getIdentifier()).toBe('cool_identifier');
  });

  test('tells if it has children', () => {
    const rawProduct = new ProductBuilder().build();

    const product = Product.create(rawProduct);

    expect(product.hasChildren()).toBe(false);
  });

  test('tells if it should have children', () => {
    const rawProduct = new ProductBuilder().build();

    const product = Product.create(rawProduct);

    expect(product.shouldHaveChildren()).toBe(false);
  });

  test('get children', () => {
    const rawProduct = new ProductBuilder().build();

    const product = Product.create(rawProduct);

    expect(product.getChildren()).toEqual([]);
  });
});

describe('>>>MODEL --- product model', () => {
  test('get label with existing locale', () => {
    const rawProductModel = new ProductModelBuilder()
      .withMeta('label', {en_US: 'My label'})
      .withIdentifier('my_awesome_indentifier')
      .build();

    const productModel = ProductModel.create(rawProductModel);

    expect(productModel.getLabel('ecommerce', 'en_US')).toBe('My label');
  });

  test('get label with existing locale', () => {
    const rawProductModel = new ProductModelBuilder()
      .withMeta('label', {en_US: 'My label'})
      .withIdentifier('my_awesome_indentifier')
      .build();

    const productModel = Product.create(rawProductModel);

    expect(productModel.getLabel('ecommerce', 'fr_FR')).toBe('my_awesome_indentifier');
  });

  test('get a completeness for the given locale and scope', () => {
    const rawProductModel = new ProductModelBuilder()
      .withMeta('completenesses', [
        {
          locale: 'de_DE',
          channel: 'ecommerce',
          ratio: 60,
          missing: 2,
          required: 5,
        },
        {
          locale: 'en_US',
          channel: 'ecommerce',
          ratio: 60,
          missing: 2,
          required: 5,
        },
      ])
      .build();

    const productModel = Product.create(rawProductModel);

    expect(productModel.getCompleteness('ecommerce', 'en_US')).toEqual({
      locale: 'en_US',
      channel: 'ecommerce',
      ratio: 60,
      missing: 2,
      required: 5,
    });
    expect(productModel.getCompleteness('ecommerce', 'fr_FR')).toEqual({});
  });

  test('get an image', () => {
    const rawProductWithImage = new ProductModelBuilder()
      .withMeta('image', {filePath: '/my/image/is/so/cool.png'})
      .build();

    const productModelWithImage = Product.create(rawProductWithImage);

    expect(productModelWithImage.getImagePath()).toBe('%2Fmy%2Fimage%2Fis%2Fso%2Fcool.png');

    const rawProductWithoutImage = new ProductModelBuilder()
      .withMeta('image', {filePath: '/my/image/is/so/cool.png'})
      .build();

    const productModelWithoutImage = Product.create(rawProductWithoutImage);

    expect(productModelWithoutImage.getImagePath()).toBe('%2Fmy%2Fimage%2Fis%2Fso%2Fcool.png');
  });

  test('get an identifier', () => {
    const rawProductModel = new ProductModelBuilder().withIdentifier('cool_identifier').build();

    const productModel = Product.create(rawProductModel);

    expect(productModel.getIdentifier()).toBe('cool_identifier');
  });

  test('tells if it has children', () => {
    const rawProductModel = new ProductModelBuilder().build();

    const productModel = Product.create(rawProductModel);

    expect(productModel.hasChildren()).toBe(false);
  });

  test('tells if it should have children', () => {
    const rawProductModel = new ProductModelBuilder().build();

    const productModel = Product.create(rawProductModel);

    expect(productModel.shouldHaveChildren()).toBe(false);
  });

  test('get children', () => {
    const rawProductModel = new ProductModelBuilder().build();

    const productModel = Product.create(rawProductModel);

    expect(productModel.getChildren()).toEqual([]);
  });
});
