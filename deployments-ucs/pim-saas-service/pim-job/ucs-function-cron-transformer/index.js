const {PubSub} = require('@google-cloud/pubsub');
const {Firestore} = require('@google-cloud/firestore');

const firestore = new Firestore({
  projectId: process.env.projectId ,
  timestampsInSnapshots: true
});

exports.publishCommand=(req, res) => {
// parse application/json
if (typeof req.body === 'object')  bodyjson=JSON.stringify(req.body)
if (typeof req.body === 'string')  bodyjson=JSON.parse(req.body);
   command  = JSON.parse(bodyjson).options;
   code =  JSON.parse(bodyjson).code;
   topicId = JSON.parse(bodyjson).topicId;
//load tenants from file store
firestore.collection(process.env.tenantContext).get()
    .then((querySnapshot) => {
      const tenantIds = [];
      querySnapshot.forEach((doc) => {
         tenantIds.push(doc.id);
      });
      if (tenantIds.length === 0) {
        return res.status(404).send({
          error: 'Unable to find the document'
        });
      }
        console.log("command===="+JSON.stringify(command));
      //read command and options jobs from the http request
      let commandJson=JSON.parse(JSON.stringify(command));
        let dataBuffer = Buffer.from(JSON.stringify(command));
        const pubSubClient = new PubSub({ projectId: process.env.projectId });
        for(var i = 0; i < tenantIds.length; i++){
          let customAttributes = {
              tenant_id: tenantIds[i],
              code: JSON.stringify(code)
            };
            const message = {
              data: dataBuffer,
              attributes: customAttributes,
            };

          //publish message into pub/sub
            let messageId =  pubSubClient
              .topic(topicId)
              .publishMessage(message).then(results => {
                  console.log("Published a message with custom attributes: messageId="+ JSON.stringify(results)+"published.***** tenant_id="+tenantIds[i]+"***** codeJob:"+JSON.stringify(code));
          }).catch(err => {
            console.error(err);

          });
          }
  res.status(200).send("Excution of batch"+code+ "finish with success");


    }).catch(err => {
      console.error(err);
      return res.status(404).send({
        error: 'Unable to retrieve the document',
        err
      });
    });


};
