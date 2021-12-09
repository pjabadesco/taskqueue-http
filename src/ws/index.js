const { createServer } = require("http");
const {Server } = require("socket.io");
const Redis = require("ioredis");
const redis = new Redis(6379, "redis");

const httpServer = createServer((req, res) => {
    res.writeHead(200, {
        'Content-Type': 'text/plain'
    });
    res.end('okay');
});
const io = new Server(httpServer, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

io.on("connection", (socket) => {
  
    socket.on("subscribe", (message) => {
        socket.emit('announcement', message.channel);

        redis.subscribe('announcement', message.channel, (err, count) => {
            if (err) {
              console.error("\n############\nFAILED TO SUBSCRIBE: %s\n############\n", err.message);
            } else {
              console.log(`\n############\nSUBSCRIBED SUCCESSFULLY! THIS CLIENT IS CURRENTLY SUBSCRIBED TO ${count} CHANNELS. ${message.channel}\n############\n`);
            }
        });        
    });

    redis.on("message", (channel, message) => {
        console.log(`\n############\nRECEIVED ${message} FROM ${channel}\n############\n`);
        socket.emit(channel, message);
    });  
    
  
  });

httpServer.listen(3000);