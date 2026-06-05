<?php

declare(strict_types=1);

namespace TestingBot\Tests\Support;

use TestingBot\Http\HttpClientInterface;
use TestingBot\Http\Request;
use TestingBot\Http\Response;

/**
 * Test double for {@see HttpClientInterface}: records every request it receives
 * and replays queued canned responses (falling back to a default 200). It can
 * also be told to simulate a transport failure.
 */
final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<Request> */
    public array $requests = [];

    /** @var list<Response> */
    private array $responses = [];

    private ?\Throwable $throwable = null;

    public function __construct(private Response $default = new Response(200, '{}'))
    {
    }

    /**
     * Queue a canned response (FIFO). Returns $this for chaining.
     */
    public function queue(Response $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    /**
     * @param array<string, string> $headers
     */
    public function queueJson(int $status, mixed $body, array $headers = []): self
    {
        return $this->queue(new Response($status, (string) json_encode($body), $headers));
    }

    /**
     * Make the next send() throw, to simulate a transport-level failure.
     */
    public function failWith(\Throwable $throwable): self
    {
        $this->throwable = $throwable;

        return $this;
    }

    public function send(Request $request): Response
    {
        $this->requests[] = $request;

        if ($this->throwable !== null) {
            $throwable = $this->throwable;
            $this->throwable = null;
            throw $throwable;
        }

        return array_shift($this->responses) ?? $this->default;
    }

    public function lastRequest(): Request
    {
        $last = end($this->requests);
        if ($last === false) {
            throw new \LogicException('No request was recorded.');
        }

        return $last;
    }
}
