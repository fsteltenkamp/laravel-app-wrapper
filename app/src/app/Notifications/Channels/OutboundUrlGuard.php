<?php

namespace App\Notifications\Channels;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Keeps user-supplied URLs away from internal services (SSRF).
 */
class OutboundUrlGuard
{
    public function allows(string $url): bool
    {
        try {
            $this->curlOptions($url);
        } catch (ChannelDeliveryFailed) {
            return false;
        }

        return true;
    }

    /**
     * Checks the URL and returns curl options that pin the host to the checked
     * address, so a DNS answer that changes before the request cannot bypass it.
     *
     * @return array<int, mixed>
     *
     * @throws ChannelDeliveryFailed if the URL may not be called
     */
    public function curlOptions(string $url): array
    {
        if (config('notifications.allow_private_hosts')) {
            return [];
        }

        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? '';

        if (! in_array($scheme, ['http', 'https'], true) || ! isset($parts['host']) || isset($parts['user'])) {
            throw new ChannelDeliveryFailed(__('This address is not allowed.'));
        }

        $host = trim($parts['host'], '[]');
        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
        $addresses = $isIp ? [$host] : (gethostbynamel($host) ?: []);

        if ($addresses === [] || array_any($addresses, fn (string $address) => IpUtils::isPrivateIp($address))) {
            throw new ChannelDeliveryFailed(__('This address is not allowed.'));
        }

        if ($isIp) {
            return [];
        }

        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        return [CURLOPT_RESOLVE => ["{$host}:{$port}:{$addresses[0]}"]];
    }
}
