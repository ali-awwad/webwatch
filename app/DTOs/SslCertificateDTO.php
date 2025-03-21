<?php

namespace App\DTOs;

class SslCertificateDTO
{
    public function __construct(
        public readonly string $commonName,
        public readonly string $organization,
        public readonly string $issuer,
        public readonly string $validFrom,
        public readonly string $validTo,
        public readonly array $sans
    ) {
    }
    
    public static function fromCertificateData(array $cert): self
    {
        $sans = [];
        if(isset($cert["extensions"]["subjectAltName"])) {
            $sansArr = explode(',', $cert["extensions"]["subjectAltName"]);
            foreach ($sansArr as $san) {
                $sans[] = ['domain' => trim($san)];
            }
        }
        return new self(
            commonName: $cert["subject"]["CN"] ?? "N/A",
            organization: $cert["subject"]["O"] ?? "N/A",
            issuer: $cert["issuer"]["CN"] ?? "N/A",
            validFrom: date("Y-m-d H:i:s", $cert["validFrom_time_t"]),
            validTo: date("Y-m-d H:i:s", $cert["validTo_time_t"]),
            sans: $sans
        );
    }
} 