var app = require('http').createServer()
var io = require('socket.io')(app);
app.listen(3000);

io.on('connection', function(socket){
  console.log('A user is connected');

  socket.on('update', function(data){
    console.log(data);
    socket.broadcast.emit('update', data);
  });
  socket.on('disconnect', function(){});
});
