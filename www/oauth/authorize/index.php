<?php declare(strict_types=1);
// Hack, https://github.com/geteduroam/ionic-app/issues/9
if (strpos($_SERVER['QUERY_STRING'], '?')) {
    parse_str(strtr($_SERVER['QUERY_STRING'],'?','&'), $_GET);
}

const POST_FIELD = 'approve';
const POST_VALUE = 'yes';

require implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 3), 'src', '_autoload.php']);

// Test this file by serving it on http://[::1]:1080/oauth/authorize/ and point your browser to:
// http://[::1]:1080/oauth/authorize/?response_type=code&code_challenge_method=S256&scope=testscope&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM&redirect_uri=http://[::1]:1234/callback/&client_id=no.fyrkat.oauth&state=0

$app = new letswifi\LetsWifiApp();
$app->registerExceptionHandler();
$realm = $app->getRealm();
$oauth = $app->getOAuthHandler( $realm );

$oauth->assertAuthorizeRequest();
$browserAuth = $app->getBrowserAuthenticator( $realm );

try {
	$user = $app->getUserFromBrowserSession( $realm );

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		$oauth->handleAuthorizePostRequest( $user->getUserID(), $_POST[POST_FIELD] === POST_VALUE );

		// handler should never return, this code should be unreachable
		header( 'Content-Type: text/plain' );
		die( "500 Internal Server Error\r\n\r\nServer error: OAuth POST request was not handled\r\n" );
	}

	$app->render( [
		'realmName' => $realm->getName(),
		'logoutUrl' => $browserAuth->getLogoutUrl(),
		'userId' => $user->getUserID(),
		'postField' => POST_FIELD,
		'postValue' => POST_VALUE,
	], 'authorize' );
} catch ( letswifi\browserauth\MismatchIdpException $e ) {
	$guessRealm = $app->guessRealm( $realm );

	$switchRealmParams = null === $guessRealm
		? $_GET
		: $_GET + ['realm' => $guessRealm->getName()]
		;

	try {
		$guessUser = $guessRealm ? $app->getUserFromBrowserSession( $guessRealm ) : null;
	} catch ( letswifi\browserauth\MismatchIdpException $e ) {
		$guessUser = null;
	}

	$app->render( [
		'realmName' => $realm->getName(),
		'guessRealmName' => $guessRealm ? $guessRealm->getName() : null,
		'guessUserId' => $guessUser ? $guessUser->getUserId() : null,
		'logoutUrl' => $browserAuth->getLogoutUrl(),

		'switchRealmLink' => $guessRealm ? '?' . http_build_query( $switchRealmParams ) : null,
		'switchUserLink' => $browserAuth->getLogoutUrl(),
		'refuseRequestLink' => $oauth->getRedirectUrlForRefusedAuthorizeRequest(),
	], 'realmchooser' );
	exit;
}
