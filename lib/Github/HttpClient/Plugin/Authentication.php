<?php

namespace Github\HttpClient\Plugin;

use Github\Client;
use Github\Exception\RuntimeException;
use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * Add authentication to the request.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Authentication implements Plugin
{
    /**
     * @var string
     */
    private $tokenOrLogin;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $method;

    /**
     * @param string      $tokenOrLogin GitHub private token/username/client ID
     * @param string|null $password     GitHub password/secret (optionally can contain $method)
     * @param string|null $method       One of the AUTH_* class constants
     */
    public function __construct($tokenOrLogin, $password, $method)
    {
        $this->tokenOrLogin = $tokenOrLogin;
        $this->password = $password;
        $this->method = $method;
    }

    /**
     * @return Promise
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $request = $request->withHeader(
            'Authorization',
            $this->getAuthorizationHeader()
        );

        return $next($request);
    }

    private function getAuthorizationHeader(): string
    {
        switch ($this->method) {
            case Client::AUTH_CLIENT_ID:
                return sprintf('Basic %s', base64_encode($this->tokenOrLogin.':'.$this->password));
            case Client::AUTH_ACCESS_TOKEN:
                return sprintf('token %s', $this->tokenOrLogin);
            case Client::AUTH_JWT:
                return sprintf('Bearer %s', $this->tokenOrLogin);
            default:
                throw new RuntimeException(sprintf('%s not yet implemented', $this->method));
        }
    }
}
