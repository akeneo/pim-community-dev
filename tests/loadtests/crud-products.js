import {uuidv4} from 'https://jslib.k6.io/k6-utils/1.4.0/index.js';
import {describe, expect} from 'https://jslib.k6.io/k6chaijs/4.3.4.1/index.js';
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

    if (response.status !== 200) {
        console.log(response.body);
    }

    expect(response.status, `API status code on authentication`).to.be.equal(200);

    return response.json();
}

export function setup() {
    // Create authentication request and return access_token
    const creds = getCredentials();
    const access_token = creds.access_token;

    const mock_product = getMockProduct(access_token);

    return {mock_product: mock_product};
}

function defaultHeaders(access_token) {
    return {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${access_token}`,
    };
}

function getMockProduct(access_token) {

    const productList = getProducts(access_token);

    let product = productList._embedded.items.pop();

    ['_links', 'created', 'updated', 'metadata', 'identifier', 'parent', 'values'].forEach(key => {
        delete product[key];
    });

    return product;
}

function createProduct(product, access_token) {
    // Call API products
    const headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.post(`${url}/api/rest/v1/products`, JSON.stringify(product), headers);
}

function getProductById(id, access_token) {
    // Call API products
    const headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.get(`${url}/api/rest/v1/products/${id}?with_attribute_options=true`, headers);

    return response.json();
}

function getProducts(access_token) {
    // Call API products
    const headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.get(`${url}/api/rest/v1/products`, headers);

    return response.json();
}

function updateProduct(product, access_token) {
    // Call API produtcts
    const headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.patch(`${url}/api/rest/v1/products/${product.identifier}`, JSON.stringify(product), headers);
}

function deleteProduct(id, access_token) {
    // Call API produtcts
    const headers = {
        headers: defaultHeaders(access_token)
    };

    const response = http.del(`${url}/api/rest/v1/products/${id}`, null, headers);
}

export default function (conf) {
    const creds = getCredentials();
    let access_token = creds.access_token;

    let mock_product = conf.mock_product;
    mock_product.identifier = uuidv4();

    createProduct(mock_product, access_token);
    let product = getProductById(mock_product.identifier, access_token);

    mock_product.enabled = false;
    updateProduct(mock_product, access_token);
    product = getProductById(mock_product.identifier, access_token);

    deleteProduct(mock_product.identifier, access_token);
}
