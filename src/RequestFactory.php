<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use Google\Protobuf\Internal\Message;
use RoadRunner\Centrifugo\Exception\InvalidRequestTypeException;
use Spiral\RoadRunner\Payload as WorkerPayload;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @psalm-type ResponseDTO = DTO\ConnectResponse|DTO\RefreshResponse|DTO\SubscribeResponse|DTO\PublishResponse|DTO\RPCResponse
 * @psalm-type RequestDTO = DTO\ConnectRequest|DTO\RefreshRequest|DTO\SubscribeRequest|DTO\PublishRequest|DTO\RPCRequest
 * @psalm-type RequestHeader = array<non-empty-string, non-empty-string[]>
 */
final class RequestFactory
{
    public function __construct(
        private readonly WorkerInterface $worker
    ) {
    }

    /**
     * Create a request Payload object.
     */
    public function createFromPayload(WorkerPayload $payload): RequestInterface
    {
        /** @var RequestHeader $headers */
        $headers = \json_decode($payload->header, true);
        $type = $headers['type'][0] ?? 'unknown';

        try {
            $typeEnum = RequestType::from($type);
        } catch (\Throwable $e) {
            throw new InvalidRequestTypeException(\sprintf('Request type `%s` is not supported', $type));
        }

        \assert($payload->body !== '');

        return match ($typeEnum) {
            RequestType::Connect => $this->createConnectRequest($payload->body, $headers),
            RequestType::Refresh => $this->createRefreshRequest($payload->body, $headers),
            RequestType::Subscribe => $this->createSubscribeRequest($payload->body, $headers),
            RequestType::Publish => $this->createPublishRequest($payload->body, $headers),
            RequestType::RPC => $this->createRPCRequest($payload->body, $headers),
        };
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     */
    private function createConnectRequest(string $body, array $headers): ConnectRequest
    {
        $request = $this->unmarshalRequestBody(DTO\ConnectRequest::class, $body);

        /** @var non-empty-string[] $channels */
        $channels = \iterator_to_array($request->getChannels()->getIterator());

        return new ConnectRequest(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            data: $request->getData() ? (array)\json_decode($request->getData(), true) : [],
            name: $request->getName(),
            version: $request->getVersion(),
            channels: $channels,
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     */
    private function createRefreshRequest(string $body, array $headers): RefreshRequest
    {
        $request = $this->unmarshalRequestBody(DTO\RefreshRequest::class, $body);

        return new RefreshRequest(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     */
    private function createSubscribeRequest(string $body, array $headers): SubscribeRequest
    {
        $request = $this->unmarshalRequestBody(DTO\SubscribeRequest::class, $body);

        return new SubscribeRequest(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            channel: $request->getChannel(),
            token: $request->getToken(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true) : [],
            data: $request->getData() ? (array)\json_decode($request->getData(), true) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     */
    private function createPublishRequest(string $body, array $headers): PublishRequest
    {
        $request = $this->unmarshalRequestBody(DTO\PublishRequest::class, $body);

        return new PublishRequest(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            channel: $request->getChannel(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true) : [],
            data: $request->getData() ? (array)\json_decode($request->getData(), true) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     */
    private function createRPCRequest(string $body, array $headers): RPCRequest
    {
        $request = $this->unmarshalRequestBody(DTO\RPCRequest::class, $body);

        return new RPCRequest(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            method: $request->getMethod(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true) : [],
            data: $request->getData() ? (array)\json_decode($request->getData(), true) : [],
            headers: $headers,
        );
    }

    /**
     * Unmarshal encoded body into GRPC DTO.
     *
     * @template T of Message
     * @param class-string<T> $class
     * @param non-empty-string $body
     * @return T
     *
     * @psalm-suppress UnsafeInstantiation
     */
    private function unmarshalRequestBody(string $class, string $body): Message
    {
        /** @var RequestDTO $request */
        $request = new $class();
        \assert($request instanceof $class);

        $request->mergeFromString($body);

        return $request;
    }
}
