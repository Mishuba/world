const WebSocket = require('ws');

// Plain WS (ws://world.tsunamiflow.club:8080)
const wsServer = new WebSocket.Server({ port: 8080 });
wsServer.on('connection', socket => {
    socket.on('message', msg => {
        wsServer.clients.forEach(client => {
            if (client !== socket && client.readyState === WebSocket.OPEN) {
                client.send(msg);
            }
        });
    });
});

