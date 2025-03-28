<?php

namespace Firebase\JWT;

require_once __DIR__ . '/JWTExceptionWithPayloadInterface.php';
use Firebase\JWT\JWTExceptionWithPayloadInterface;


class ExpiredException extends \UnexpectedValueException implements JWTExceptionWithPayloadInterface
{
    private object $payload;

    public function setPayload(object $payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): object
    {
        return $this->payload;
    }
}