const { createServer } = require("http");
const { Server } = require("socket.io");
const { createAdapter } = require("@socket.io/redis-adapter");
const { createClient } = require("redis");

const httpServer = createServer((req, res) => {
  res.writeHead(200, { 'Content-Type': 'text/plain' });
  res.end('okay');
});
const io = new Server(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});        

const pubClient = createClient({ host: "redis", port: 6379 });
const subClient = pubClient.duplicate();

io.emit('hello', 'to all clients');

io.on("connection", (socket) => {
    // setInterval(function () {
    //     socket.emit('pubsub', 'Hello World!');
    // }, 3000);    

    io.on("joinRoom", (key) => {
        socket.join(key);
    });    

    io.on("message", (key) => {
        socket.join(key);
    });    

});


io.adapter(createAdapter(pubClient, subClient));
httpServer.listen(3000);   