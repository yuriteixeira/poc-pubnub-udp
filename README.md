# What does it do? #

To test pubnub message delivery and reception reliability and how long does it take to do a full "roundtrip" (message sent by point A, message listened by point B and message redirected and updated on point A).

# How does it work? #

    [ MESSAGE SENDER ] --- store message on mongo and send it to pubnub channel(s)

            |
            |
            v

    [ MESSAGE LISTENER ] --- will listen to messages on a informed pubnub channel and redirect it to udp server

            |
            |
            v

    [ UDP SERVER ] --- receives message and updates previously stored messages with the time that message was updated

# Install Instructions #

* Install php
* Install node
* Install npm
* Install mongodb
* Create a free account on [pubnub](http://pubnub.com)
* Copy file `config/config.json.template` to a new file called `config/config.json`and replace the pubnub related values on your [account profile](https://pubnub-prod.appspot.com/account) 

	**(NOTE: if you want to distribute mongo, udpserver and listener in different hosts, no problem! just replace the values pointing to "localhost" to each desired host on config file)**

* Run `npm install` (will install packages coffee-script and mongodb)
* Run `./node_modules/.bin/coffee` bin/udp_server.coffee" (to startup udp server)
* Run `php bin/message_listener.php` (a php that will listen to pubnub messages and redirect to udp server)
* Run `php bin/message_sender.php` (a php that will store a message on mongo and send it to pubnub channel)

#Checking results#

* Access the mongo database (see your config/config.json file)

* Run this command: `db.messages.find()` (if you changed the collection used to store messages on config/config.json, please use it on command). You will see something like this:

        {
            "_id": ObjectId("50c0cdc4dc0c7c6faa64ec7e"),
            "channel": "xxx",
            "history": [
                {
                    "received_at": ISODate("2012-12-06T17:29:30.276Z"),
                    "sent_at": ISODate("2012-12-06T16:54:28.426Z"),
                    "sent_at_timestamp": 1354812868
                }
            ]
        }

* check how much messages have the field `received_at` different than `null` and how long does it take, checking the field `sent_at`;

