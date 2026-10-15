<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_proxy-test', fn (Request $request) => [
            'ip' => $request->ip(),
            'secure' => $request->secure(),
            'host' => $request->getHost(),
        ]);
    }

    private function getThroughProxy(string $proxyIp)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $proxyIp])
            ->withHeaders([
                'X-Forwarded-For' => '1.1.1.1',
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'app.example.com',
            ])
            ->get('/_proxy-test');
    }

    public function test_forwarded_headers_are_ignored_by_default(): void
    {
        config(['trustedproxy.proxies' => null]);

        $this->getThroughProxy('172.18.0.2')
            ->assertJson(['ip' => '172.18.0.2', 'secure' => false])
            ->assertJsonMissing(['host' => 'app.example.com']);
    }

    public function test_private_subnets_are_trusted(): void
    {
        config(['trustedproxy.proxies' => 'PRIVATE_SUBNETS']);

        $this->getThroughProxy('172.18.0.2')
            ->assertJson(['ip' => '1.1.1.1', 'secure' => true, 'host' => 'app.example.com']);
    }

    public function test_public_peers_are_not_trusted_with_private_subnets(): void
    {
        config(['trustedproxy.proxies' => 'PRIVATE_SUBNETS']);

        $this->getThroughProxy('8.8.8.8')
            ->assertJson(['ip' => '8.8.8.8', 'secure' => false]);
    }

    public function test_a_comma_separated_list_is_trusted(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.1, 192.168.1.0/24']);

        $this->getThroughProxy('192.168.1.20')
            ->assertJson(['ip' => '1.1.1.1', 'secure' => true]);
    }
}
