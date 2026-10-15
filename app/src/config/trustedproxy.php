<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Proxies (e.g. Traefik, nginx, a load balancer) whose X-Forwarded-*
    | headers are trusted for the client IP, scheme, host and port. Read by
    | Laravel's TrustProxies middleware.
    |
    | Comma-separated IPs/CIDRs, "PRIVATE_SUBNETS" for all private ranges,
    | "*" for any proxy, or empty to trust none.
    |
    */

    'proxies' => env('TRUSTED_PROXIES') ?: null,

];
