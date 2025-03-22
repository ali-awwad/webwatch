<?php

namespace App\Actions;

use App\DTOs\SslCertificateDTO;

class GetSslCertificateAction
{

    public static function execute($domain): SslCertificateDTO
    {
        $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);

        // Open a connection to fetch the certificate
        $client = stream_socket_client(
            "ssl://{$domain}:443",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$client) {
            throw new \Exception("Failed to connect: $errstr ($errno)");
        }

        $params = stream_context_get_params($client);
        $cert = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

        return SslCertificateDTO::fromCertificateData($cert);
    }
}