const {Firestore} = require('@google-cloud/firestore');
const CryptoJS = require("crypto-js");
const functions = require('@google-cloud/functions-framework');

const firestore = new Firestore({
  projectId: process.env.fireStoreProjectId,
  timestampsInSnapshots: true
});

functions.http('createDocument', (req, res) => {
// parse application/json
  const bodyJson = req.body;
  const tenantId = bodyJson.tenant_id;
  const tenantName = bodyJson.tenant_name;
  const mysqlPassword = bodyJson.mysql_password;
  const emailPassword = bodyJson.email_password;

  //check body params
  if (!tenantId || !tenantName || !mysqlPassword || !emailPassword) {
    res.status(402).send(" params empty, null or not exist  !!!");
  }

  //check envar
  const domain = process.env.domain;
  const projectId = process.env.projectId;
  const mailerBaseDsn = process.env.mailerBaseDsn;

  if (!domain || !projectId || !mailerBaseDsn) {
    res.status(402).send(" env variable empty !!!");
  }

  tenantContextData = JSON.stringify({
    tenantName: {
      "AKENEO_PIM_URL": "https://" + tenantName + "." + domain,
      "APP_DATABASE_HOST": "pim-mysql." + tenantId + ".svc.cluster.local",
      "APP_INDEX_HOSTS": "elasticsearch-client." + tenantId + ".svc.cluster.local",
      "APP_TENANT_ID": tenantId,
      "MAILER_PASSWORD": emailPassword,
      "MAILER_DSN": mailerBaseDsn + "?encryption=tls&auth_mode=login&username=" + tenantName + "-" + projectId + "@mg.cloud.akeneo.com&password=" + emailPassword,
      "MAILER_FROM": "Akeneo <no-reply%40" + tenantName + "." + domain + ">",
      "MAILER_USER": tenantName + "-" + projectId + "@mg.cloud.akeneo.com",
      "MEMCACHED_SVC": "memcached." + tenantId + ".svc.cluster.local",
      "APP_DATABASE_PASSWORD": mysqlPassword,
      "PFID": tenantId,
      "SRNT_GOOGLE_BUCKET_NAME": tenantId
    }
  });

  const encryptKey = process.env.TENANT_CONTEXT_ENCRYPTION_KEY;

  async function encryptAES(inputText, key) {
    return CryptoJS.AES.encrypt(inputText, key).toString();
  }

  encryptAES(tenantContextData, encryptKey).then((response) => {
    const data = JSON.stringify(
      {
        "status": "creation_in_progress",
        "status_date": new Date().toISOString(),
        "context": response.toString()
      }
    );

    const docRef = firestore.collection(process.env.tenantContext).doc(tenantName).set(JSON.parse(data), {merge: true});
  });

  async function decryptAES(encryptedContext, encryptKey) {
    return CryptoJS.AES.decrypt(encryptedContext, encryptKey);
  };
//TODO : if we need to decrypt document
  /*
  let encryptedContext=" get my content from some where"
    decryptAES(encryptedContext, encryptKey).then((response) =>{
      const decrypted =response.toString()
    });
  */

  res.status(200).send(" the document create with success !!!");
});
