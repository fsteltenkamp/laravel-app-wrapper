<?php

namespace App\Notifications\Channels;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

abstract class HttpChannelDriver implements ChannelDriver
{
    public function __construct(protected OutboundUrlGuard $guard) {}

    /**
     * Validation rule rejecting URLs the guard would refuse to call.
     */
    protected function allowedUrlRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || ! $this->guard->allows($value)) {
                $fail(__('The :attribute must be a publicly reachable address.'));
            }
        };
    }

    /**
     * @param  array<string, string>  $headers
     *
     * @throws ChannelDeliveryFailed
     */
    protected function postJson(string $url, string $body, array $headers = []): void
    {
        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withoutRedirecting()
                ->withOptions(['curl' => $this->guard->curlOptions($url)])
                ->withHeaders($headers)
                ->withBody($body, 'application/json')
                ->post($url);
        } catch (ConnectionException) {
            throw new ChannelDeliveryFailed(__('Could not connect to the service.'));
        }

        if (! $response->successful()) {
            throw new ChannelDeliveryFailed(__('The service answered with HTTP :status.', ['status' => $response->status()]));
        }
    }
}
