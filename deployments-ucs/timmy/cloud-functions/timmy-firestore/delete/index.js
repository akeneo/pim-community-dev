const {Firestore} = require('@google-cloud/firestore');
const functions = require('@google-cloud/functions-framework');

const firestore = new Firestore({
  projectId: process.env.fireStoreProjectId,
  timestampsInSnapshots: true
});

functions.http('deleteDocument', async (req, res) => {
  let docRef = req.body.docRef;
  if (!docRef) {
    res.status(402).send(" missing docRef !!!");
  }
  const document = firestore.collection(process.env.tenantContext).doc(docRef);

  const snapshot = await document.get()
  if (snapshot.exists) {
    // Delete the document.
    document.delete().then(() => {
      return res.status(200).send('Document was successfully delete');
    }).catch((error) => {
      console.error("Error removing document:", error);
      return res.status(402).send(error);
    });
  } else {
    return res.status(200).send("Document doesn't exists");
  }
});
