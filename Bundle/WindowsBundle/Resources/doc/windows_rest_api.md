Windows REST API
========================

### Available REST API Methods

API URL ```/api/rest/<version|latest>/windows```

### GET

Get all Windows States for user

Response format:
``` javascript
[{id: stateId, data: stateData}, ...]
```

### POST

Add Windows State

Request format:
``` javascript
{data: jsonEncodedObject}
```

Response:
``` javascript
{id: stateId}
```

### PUT

Update record in state storage

Request:
``` javascript
{data: jsonEncodedObject}
```

### DELETE

Remove Windows state
