import {expect} from 'https://jslib.k6.io/k6chaijs/4.3.4.1/index.js';
import http from 'k6/http';
import encoding from 'k6/encoding';

const protocol = `${__ENV.SECURE}` == 0 ? `http` : `https`;
const url = `${protocol}://${__ENV.FQDN}`;
const credentials = `${__ENV.API_CLIENT_ID}:${__ENV.API_SECRET}`;
const encodedCredentials = encoding.b64encode(credentials);
const k6_duration = `${__ENV.K6_DURATION}` || '10m';
const k6_vus = `${__ENV.K6_VUS}` || 10;

export const options = {
    duration: `${k6_duration}`,
    vus: `${k6_vus}`,
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(90)<5000'],
        http_req_waiting: ['p(90)<5000'],
    },
};

function getCredentials() {
    // Create authentication request and return access_token
    const headers = {
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Basic ${encodedCredentials}`,
        },
    };
    const data = {
        "username": `${__ENV.API_USERNAME}`,
        "password": `${__ENV.API_PASSWORD}`,
        "grant_type": "password"
    };

    const response = http.post(`${url}/api/oauth/v1/token`, JSON.stringify(data), headers);

    expect(response.status, `API status code on authentication`).to.be.equal(200);

    return response.json();
}

export function setup() {
    // Create authentication request and return access_token
    const creds = getCredentials();
    const access_token = creds.access_token;

    //create an asset family
    const mockAssetFamily = getMockAssetFamily();
    createAssetFamily(mockAssetFamily, access_token);

    //create attribute into catalog structure of Product
    const mockAttributeProduct = getMockAttributeProduct(mockAssetFamily.code);
    createAttributeProductLinkToAssetFamily(mockAttributeProduct, access_token);

    //Create a family Product link to Attribute
    const mockFamilyProduct = getMockFamilyProduct(mockAttributeProduct.code);
    createFamilyProductLinkToAttribute(mockFamilyProduct, access_token);

    const family_code = mockFamilyProduct.code;

    return {"familyCode": family_code};
}

function defaultHeaders(access_token) {
    return {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${access_token}`,
    };
}

function getMockAssetFamily() {
    let n = (Math.random() + 1).toString(36).substring(7);
    return {
        "code": "model_pictures_" + n,
        "labels": {"en_US": "Designer"}
    }
}

function createAssetFamily(assetFamily, access_token) {
    //Call API Asset Family
    let headers = {
        headers: defaultHeaders(access_token)
    };

    let response = http.post(`${url}/api/rest/v1/asset-families/${assetFamily.code}`, JSON.stringify(assetFamily), headers);

    expect(response.status, `API status code on creation Asset Family`).to.equal(201);
}

function getMockAttributeProduct(assetFamilyCode) {
    let n = (Math.random() + 1).toString(36).substring(7);
    return {
        "code": "allie_model_pictures" + n,
        "type": "pim_catalog_asset_collection",
        "group": "Other",
        "reference_data_name": assetFamilyCode
    }
}

function createAttributeProductLinkToAssetFamily(attribute, access_token) {
    //Call API Asset
    let headers = {
        headers: defaultHeaders(access_token)
    };

    let response = http.post(`${url}/api/rest/v1/attributes`, JSON.stringify(attribute), headers);

    expect(response.status, `API status code on creation Attribute with Asset Family`).to.equal(201);
}

function getMockFamilyProduct(attributeProduct) {
    let n = (Math.random() + 1).toString(36).substring(7);
    return {
        "code": "model_pictures_Family" + n,
        "attributes": [
            attributeProduct
        ]
    }
}

function createFamilyProductLinkToAttribute(familyProduct, access_token) {
    //Call API Asset
    let headers = {
        headers: defaultHeaders(access_token)
    };

    let response = http.post(`${url}/api/rest/v1/families`, JSON.stringify(familyProduct), headers);

    expect(response.status, `API status code on creation Asset`).to.equal(201);
}

function getAllProducts(access_token, page = 1) {
    let headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.get(`${url}/api/rest/v1/products?page=${page}`, headers);

    return response.json();
}

function getProductById(id, access_token) {
    // Call API products
    const headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.get(`${url}/api/rest/v1/products/${id}`, headers);

    let product = null;
    if (response.status === 200) {
        product = response.json();
    }

    return product;
}

function updateProductById(product, access_token) {
    // Call API products
    let headers = {
        headers: defaultHeaders(access_token)
    };

    let response = http.patch(`${url}/api/rest/v1/products/${product.identifier}`, JSON.stringify(product), headers);

    // Current user doesn't have ownership on product
    if(response.status === 403) {
        return null;
    }
}

export default function (data) {
    const creds = getCredentials();
    let access_token = creds.access_token;

    //retrieve code Asset Family
    const codeFamily = data.familyCode;
    //put asset family to products/ update products
    let listAllProducts = [];
    //loop on products and update
    let page = 1;
    do {
        listAllProducts = getAllProducts(access_token, page);
        if (listAllProducts == null) {
            break;
        }
        listAllProducts["_embedded"]["items"].forEach((item) => {
            let product = getProductById(item.identifier, access_token);

            if (product === null) {
                return;
            }

            if (product.parent === null && product.enabled === true) {
                product.family = codeFamily;
            }

            updateProductById(product, access_token);
        })

        if (listAllProducts["_links"]["next"] !== undefined) {
            page++;
        } else {
            page = null;
        }

    } while (page !== null);
}
