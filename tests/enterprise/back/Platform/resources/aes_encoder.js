/*
* Simple JS lib to generate AES 256 crypted payload
*
* Usage : `docker-compose run --rm node node grth/tests/back/Platform/resources/aes_encoder.js`
*
* Output example: {"data":"XMWZfti6Ldl5qXiThWuItB7hLkZY1/eTin/GH96HASIn8aCnXhc1C+r5fDuHbzNO","iv":"041a26b9f9e729082765b9634a5fb591"}
*/
const crypto = require('crypto')

const algorithm = "aes-256-cbc"
const initVector = crypto.randomBytes(16)

const base64Key = 'NDyClnH/qM6JfUR7c8Yc0kKBhaqP554EpHha4HTHQ/Y='
const securityKey = Buffer.from(base64Key, 'base64')

const dataToEncode = '{"updated_context": "updated_value"}'

const cipher = crypto.createCipheriv(algorithm, securityKey, initVector)

let encryptedData = cipher.update(dataToEncode, "utf-8", "base64")
encryptedData += cipher.final("base64")

console.debug('-'.repeat(80))
console.debug(JSON.stringify({
    data: encryptedData,
    iv: initVector.toString('hex'),
}))
console.debug('-'.repeat(80))
