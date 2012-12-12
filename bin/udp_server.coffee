dgram = require "dgram"
fileSystem = require "fs"
MongoDb = require "mongodb"

print = (message) -> console.log "\n>>> UDP SERVER: " + message

configContent = fileSystem.readFileSync(__dirname + "/../config/config.json", "utf8")
config = JSON.parse configContent

mongoServer = new MongoDb.Server(config.db.host, config.db.port)
mongoDatabase = new MongoDb.Db(config.db.name, mongoServer, config.db.options)

mongoDatabase.open (error, client) ->

    collection = new MongoDb.Collection(client, config.db.collection)

    udpServer = dgram.createSocket "udp4"
    udpServer
        .on("listening", -> print "RUNNING!")
        .on("message", (messageContent, info) ->

            try

                message = JSON.parse messageContent.toString()
                now = new Date()

                criteria =
                    "channel": message.channel
                    "history.sent_at_timestamp": message.sent_at_timestamp

                collection.update(
                    criteria,
                    {"$set": {"history.$.received_at": new Date()}},
                    {safe: true},
                    (error, updatedDocuments) ->

                        print "Message received: " + message
                        print "Updated documents:" + updatedDocuments
                )

            catch e

                print "ERROR:\n#{e}\n-----"
        )
        .bind config.udp.port
