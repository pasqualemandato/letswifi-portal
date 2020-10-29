# Let's Wifi Certificate Authority

This is the reference CA for geteduroam.  It is intended to be used with an app such as [ionic-app](https://github.com/geteduroam/ionic-app).  The process is as follows:

* The app sends the user to /oauth/authorize/ with additional GET parameters
* The user is asked to log in or redirected to an SSO service
* After logging in, the user is redirected to a callback URL from the app
* The app has obtained an authorization_code, which it uses to retrieve an access_code
* The access_code is used to generate an [eap-config](https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02) file containing user credentials
* The app installs the eap-config file
* The server logs the public key material generated


## Getting up and running quick 'n dirty

Upload this whole project to a webserver, and make `www/` accessible, ideally as the root

This quick'n'dirty guide assumes you'll be using SimpleSAMLphp (the only authentication method supported ATM)

	make simplesamlphp


Initialize the SQLite database (should be possible to use other SQL databases, but this is not tested)

	mkdir var
	sqlite3 var/letswifi-dev.sqlite <sql/letswifi-dev.sqlite.sql


Copy etc/letswifi.conf.simplesaml.php etc/letswifi.conf.php and change `userIdAttribute` to match your setup.


Initialize required submodules

        make submodule


Create the realm with a default client certificate validity of one year

	bin/add-realm.php example.com 365


Write metadata of your SAML IdP to simplesamlphp/metadata/saml20-idp-remote.php

Navigate to https://example.com/simplesaml/module.php/saml/sp/metadata.php/default-sp?output=xhtml to get the metadata of the service, and register it in your IdP


## Running a development server

	make simplesamlphp
	make dev


### Testing manually

There is a [shell script to initiate an OAuth flow](https://github.com/geteduroam/geteduroam-sh)

	./geteduroam.sh 'http://[::1]:1080' example.com >test.eap-config

* If everything went fine, you get an eap-config XML payload in test.eap-config
* You will see the public key material logged in the `tlscredential` SQL table


## Contributing

Before committing, please run

	make camera-ready
