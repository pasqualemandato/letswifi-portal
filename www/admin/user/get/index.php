<?php declare(strict_types=1);

/*
 * This file is part of letswifi; a system for easy eduroam device enrollment
 *
 * Copyright: 2018-2021, Jørn Åne de Jong, Uninett AS <jornane.dejong@surf.nl>
 * Copyright: 2020-2021, Paul Dekkers, SURF <paul.dekkers@surf.nl>
 * SPDX-License-Identifier: BSD-3-Clause
 */

require \implode(\DIRECTORY_SEPARATOR, [\dirname(__DIR__, 4), 'src', '_autoload.php']);

$user = $_POST['user'] ?? $_GET['user'];
$subject = $_POST['subject'] ?? $_GET['subject'];
if ( !\is_string( $user ) && !\is_string( $subject ) ) {
	\header( 'Content-Type: text/plain', true, 400 );
	exit( "400 Bad Request\r\n\r\nMissing GET parameter user or subject\r\n" );
}

$app = new letswifi\LetsWifiApp();
$app->registerExceptionHandler();

$app->requireAdmin( 'admin-user-get' );

$realmManager = $app->getRealmManager();
$realm = $app->getRealm();

if ( $user ) {
	$certificates = $realmManager->listUserCertificates( $realm->getName(), $user );
	$queryVars = ['user' => $user];
} else {
	$certificate = $realmManager->getCertificate( $realm->getName(), $subject );
	if ( null === $certificate ) {
		\header( 'Content-Type: text/plain', true, 404 );
		exit( "404 Not Found\r\n\r\nNo certificate found with subject ${subject}\r\n" );
	}
	$user = $certificate['requester'];
	$certificates = [$certificate];
	$userQueryVars = ['user' => $user];
	$queryVars = ['subject' => $subject];
}

$app->render( [
	'href' => '/admin/user/get/?' . \http_build_query( $queryVars ),
	'jq' => '.certificates | map(del(.csr,.x509))',
	// TSV seems like fun, but it looks like empty columns disappear
	//'jq' => '.certificates[] | [.serial, .requester, .sub, .issued, .expires, .revoked, .usage, .client] | @tsv',
	'certificates' => $certificates,
	'user' => ['name' => $user],
	'viewAll' => isset( $userQueryVars ) ? '?' . \http_build_query( $userQueryVars ) : null,
	'form' => [
		'realm' => $realm->getName(),
		'action' => '../../ca/revoke/',
		'revokeAll' => !$subject,
	],
], 'admin-user-get' );
