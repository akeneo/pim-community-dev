import generate from 'akeneopimenrichmentassetmanager/assets-collection/application/value-generator';
import {fetchAssetAttributes} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute';
import {fetchPermissions} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/permission';
import {
  AttributeGroupPermission,
  LocalePermission,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/permission';
import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {CategoryCode} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';

jest.mock('pim/fetcher-registry', () => {});
fetchAssetAttributes = jest.fn();
fetchPermissions = jest.fn();

test('It should generate a value collection from the product with all attributes editable', async () => {
  // The values returns are editable
  const attributeGroupPermission = {
    code: 'marketing',
    view: true,
    edit: true,
  };
  const localePermission = {
    code: 'en_US',
    view: true,
    edit: true,
  };
  const isReadOnly = false;
  const attributesForThisLevel = ['packshot', 'notices'];
  const categoriesEditPermission = ['scanners'];

  fetchAssetAttributes.mockImplementation(() => getMockAssetAttributes(isReadOnly));
  fetchPermissions.mockImplementation(() =>
    getMockPermissions(attributeGroupPermission, localePermission, categoriesEditPermission)
  );
  const product = getMockProduct(attributesForThisLevel);
  const expectedValueCollection = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone'],
      editable: true,
    },
    {
      attribute: {
        code: 'notices',
        labels: {
          en_US: 'Notices',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'notices',
      },
      locale: null,
      channel: 'ecommerce',
      data: [],
      editable: true,
    },
  ];

  const valueCollection = await generate(product);
  expect(valueCollection).toEqual(expectedValueCollection);
});

test('It should generate a value collection from the product with an attribute group non editable', async () => {
  // The attribute group doesn't have the edit permission
  const attributeGroupPermission = {
    code: 'marketing',
    view: true,
    edit: false,
  };
  const localePermission = {
    code: 'en_US',
    view: true,
    edit: true,
  };
  const isReadOnly = false;
  const attributesForThisLevel = ['packshot', 'notices'];
  const categoriesEditPermission = ['scanners'];

  fetchAssetAttributes.mockImplementation(() => getMockAssetAttributes(isReadOnly));
  fetchPermissions.mockImplementation(() =>
    getMockPermissions(attributeGroupPermission, localePermission, categoriesEditPermission)
  );
  const product = getMockProduct(attributesForThisLevel);
  const expectedValueCollection = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone'],
      editable: false, // so the values aren't editable
    },
    {
      attribute: {
        code: 'notices',
        labels: {
          en_US: 'Notices',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'notices',
      },
      locale: null,
      channel: 'ecommerce',
      data: [],
      editable: false, // so the values aren't editable
    },
  ];

  const valueCollection = await generate(product);
  expect(valueCollection).toEqual(expectedValueCollection);
});

test('It should generate a value collection from the product with a locale non editable', async () => {
  const attributeGroupPermission = {
    code: 'marketing',
    view: true,
    edit: true,
  };
  // The locale for the packshot doesn't have the edit permission 
  const localePermission = {
    code: 'en_US',
    view: true,
    edit: false,
  };
  const isReadOnly = false;
  const attributesForThisLevel = ['packshot', 'notices'];
  const categoriesEditPermission = ['scanners'];

  fetchAssetAttributes.mockImplementation(() => getMockAssetAttributes(isReadOnly));
  fetchPermissions.mockImplementation(() =>
    getMockPermissions(attributeGroupPermission, localePermission, categoriesEditPermission)
  );
  const product = getMockProduct(attributesForThisLevel);
  const expectedValueCollection = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone'],
      editable: false, // so the value packshot isn't editable
    },
    {
      attribute: {
        code: 'notices',
        labels: {
          en_US: 'Notices',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'notices',
      },
      locale: null,
      channel: 'ecommerce',
      data: [],
      editable: true,
    },
  ];

  const valueCollection = await generate(product);
  expect(valueCollection).toEqual(expectedValueCollection);
});

test('It should generate a value collection from the product with a read only attribute', async () => {
  const attributeGroupPermission = {
    code: 'marketing',
    view: true,
    edit: true,
  };
  const localePermission = {
    code: 'en_US',
    view: true,
    edit: true,
  };
  // The attributes are read only 
  const isReadOnly = true;
  const attributesForThisLevel = ['packshot', 'notices'];
  const categoriesEditPermission = ['scanners'];

  fetchAssetAttributes.mockImplementation(() => getMockAssetAttributes(isReadOnly));
  fetchPermissions.mockImplementation(() =>
    getMockPermissions(attributeGroupPermission, localePermission, categoriesEditPermission)
  );
  const product = getMockProduct(attributesForThisLevel);
  const expectedValueCollection = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: true,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone'],
      editable: false, // so the values aren't editable
    },
    {
      attribute: {
        code: 'notices',
        labels: {
          en_US: 'Notices',
        },
        group: 'marketing',
        isReadOnly: true,
        referenceDataName: 'notices',
      },
      locale: null,
      channel: 'ecommerce',
      data: [],
      editable: false, // so the values aren't editable
    },
  ];

  const valueCollection = await generate(product);
  expect(valueCollection).toEqual(expectedValueCollection);
});

test('It should generate a value collection from the product with a non editable parent attribute', async () => {
  const attributeGroupPermission = {
    code: 'marketing',
    view: true,
    edit: true,
  };
  const localePermission = {
    code: 'en_US',
    view: true,
    edit: true,
  };
  const isReadOnly = false;
  // The packshot attribute is not an attribute for this product level
  const attributesForThisLevel = ['notices'];
  const categoriesEditPermission = ['scanners'];

  fetchAssetAttributes.mockImplementation(() => getMockAssetAttributes(isReadOnly));
  fetchPermissions.mockImplementation(() =>
    getMockPermissions(attributeGroupPermission, localePermission, categoriesEditPermission)
  );
  const product = getMockProduct(attributesForThisLevel);
  const expectedValueCollection = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone'],
      editable: false, // so the value packshot isn't editable
    },
    {
      attribute: {
        code: 'notices',
        labels: {
          en_US: 'Notices',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'notices',
      },
      locale: null,
      channel: 'ecommerce',
      data: [],
      editable: true,
    },
  ];

  const valueCollection = await generate(product);
  expect(valueCollection).toEqual(expectedValueCollection);
});

test('It should generate a value collection from the product with a non editable category', async () => {
  const attributeGroupPermission = {
    code: 'marketing',
    view: true,
    edit: true,
  };
  const localePermission = {
    code: 'en_US',
    view: true,
    edit: true,
  };
  const isReadOnly = false;
  const attributesForThisLevel = ['packshot', 'notices'];
  // The category scanner doesn't have the edit permission
  const categoriesEditPermission = [];

  fetchAssetAttributes.mockImplementation(() => getMockAssetAttributes(isReadOnly));
  fetchPermissions.mockImplementation(() =>
    getMockPermissions(attributeGroupPermission, localePermission, categoriesEditPermission)
  );
  const product = getMockProduct(attributesForThisLevel);
  const expectedValueCollection = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone'],
      editable: false, // so the values aren't editable
    },
    {
      attribute: {
        code: 'notices',
        labels: {
          en_US: 'Notices',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'notices',
      },
      locale: null,
      channel: 'ecommerce',
      data: [],
      editable: false, // so the values aren't editable
    },
  ];

  const valueCollection = await generate(product);
  expect(valueCollection).toEqual(expectedValueCollection);
});

const getMockAssetAttributes = (isReadOnly: boolean) => {
  return [
    {
      code: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      group: 'marketing',
      isReadOnly,
      referenceDataName: 'packshot',
    },
    {
      code: 'notices',
      labels: {
        en_US: 'Notices',
      },
      group: 'marketing',
      isReadOnly,
      referenceDataName: 'notices',
    },
  ];
};

const getMockPermissions = (
  attributeGroupPermission: AttributeGroupPermission,
  localePermission: LocalePermission,
  categoriesEditPermission: CategoryCode[]
) => {
  return {
    attributeGroups: [attributeGroupPermission],
    locales: [localePermission],
    categories: {
      EDIT_ITEMS: categoriesEditPermission,
    },
  };
};

const getMockProduct = (attributesForThisLevel: AttributeCode[]) => {
  return {
    meta: {
      attributes_for_this_level: attributesForThisLevel,
      level: 1,
      completeness: {
        channel: 'ecommerce',
        labels: {en_US: 'E-commerce'},
        locales: {
          en_US: {
            completeness: {
              required: 2,
              missing: 1,
            },
          },
        },
      },
    },
    values: {
      packshot: [
        {
          data: ['iphone'],
          scope: 'ecommerce',
          locale: 'en_US',
        },
      ],
      notices: [
        {
          data: [],
          scope: 'ecommerce',
          locale: null,
        },
      ],
    },
    categories: ['scanners', 'xerox'],
  };
};
