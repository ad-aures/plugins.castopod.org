<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Throttle\Throttler;
use Exception;

class Throttle implements FilterInterface
{
    /**
     * This is a demo implementation of using the Throttler class
     * to implement rate limiting for your application.
     *
     * @param list<string>|null $arguments
     *
     * @return ResponseInterface|null
     *
     * @phpstan-ignore typeCoverage.paramTypeCoverage,typeCoverage.returnTypeCoverage
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        /** @var Throttler $throttler */
        $throttler = service('throttler');

        // Restrict an IP address to no more than 1 request
        // per second across the entire site.
        if ($throttler->check($this->obfuscateIP($request->getIPAddress()), 60, MINUTE) === false) {
            return service('response')->setStatusCode(429);
        }

        return null;
    }

    /**
     * We don't have anything to do here.
     *
     * @param list<string>|null $arguments
     *
     * @phpstan-ignore typeCoverage.paramTypeCoverage,typeCoverage.returnTypeCoverage
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    /**
     * Anonymizes and hashes a given IP for GDPR compliancy.
     *
     * @return string The obfuscated IP
     */
    private function obfuscateIP(string $ip): string
    {
        $anonymizedIP = '';
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';  // Zero out last octet

            $anonymizedIP = implode('.', $parts);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Convert IPv6 to binary string representation
            $bin = inet_pton($ip);
            if ($bin === false) {
                throw new Exception('Could not convert IPv6 to binary string representation.');
            }

            // Zero last 80 bits (10 bytes)
            // IPv6 is 16 bytes, keep first 6 bytes as-is, zero last 10 bytes
            $bin = substr($bin, 0, 6) . str_repeat('0', 10);

            // Convert back to IPv6 string
            $anonymizedIp = inet_ntop($bin);

            if (! $anonymizedIp) {
                throw new Exception('Could not convert binary string representation to IPv6.');
            }
        } else {
            throw new Exception('Invalid IP.');
        }

        $salt = $this->getRotatingSalt();

        return hash('sha256', $salt . '|' . $anonymizedIP);
    }

    /**
     * Retrieves a randomly generated salt stored in cache for a DAY
     */
    private function getRotatingSalt(): string
    {
        $cacheName = 'throttle-salt';
        if (! ($found = cache($cacheName))) {
            // Generate a cryptographically secure random part (32 bytes)
            $randomPart = random_bytes(32);

            $found = hash('sha256', $randomPart);

            cache()
                ->save($cacheName, $found, DAY);
        }

        /** @var string $found */
        return $found;
    }
}
