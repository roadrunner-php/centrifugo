<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use JsonException;
use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use Google\Protobuf\Internal\Message;
use RoadRunner\Centrifugo\Exception\InvalidRequestTypeException;
use Spiral\RoadRunner\Payload as WorkerPayload;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @psalm-type ResponseDTO = DTO\ConnectResponse|DTO\RefreshResponse|DTO\SubscribeResponse|DTO\PublishResponse|DTO\RPCResponse|DTO\SubRefreshResponse
 * @psalm-type RequestDTO = DTO\ConnectRequest|DTO\RefreshRequest|DTO\SubscribeRequest|DTO\PublishRequest|DTO\RPCRequest|DTO\SubRefreshRequest
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
     * @throws JsonException
     * @throws InvalidRequestTypeException
     * @psalm-suppress InternalProperty
     */
    public function createFromPayload(WorkerPayload $payload): RequestInterface
    {
        /** @var RequestHeader $headers */
        $headers = \json_decode($payload->header, true, 512, JSON_THROW_ON_ERROR);
        $type = $headers['type'][0] ?? 'unknown';

        try {
            $typeEnum = RequestType::from($type);
        } catch (\Throwable) {
            throw new InvalidRequestTypeException(
                $payload,
                \sprintf('Request type `%s` is not supported', $type)
            );
        }

        \assert($payload->body !== '');

        return match ($typeEnum) {
            RequestType::Connect => $this->createConnectRequest($payload->body, $headers),
            RequestType::Refresh => $this->createRefreshRequest($payload->body, $headers),
            RequestType::SubRefresh => $this->createSubRefreshRequest($payload->body, $headers),
            RequestType::Subscribe => $this->createSubscribeRequest($payload->body, $headers),
            RequestType::Publish => $this->createPublishRequest($payload->body, $headers),
            RequestType::RPC => $this->createRPCRequest($payload->body, $headers),
            RequestType::Invalid => throw new InvalidRequestTypeException(
                $payload,
                \sprintf('Request type `%s` is not supported', $type)
            )
        };
    }

    public function createFromException(\Throwable $e): Invalid
    {
        return new Invalid(
            exception: $e,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     * @throws JsonException
     */
    private function createConnectRequest(string $body, array $headers): Connect
    {
        $request = $this->unmarshalRequestBody(DTO\ConnectRequest::class, $body);

        /** @var non-empty-string[] $channels */
        $channels = \iterator_to_array($request->getChannels()->getIterator());

        return new Connect(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            data: $request->getData() ? (array)\json_decode($request->getData(), true, 512, JSON_THROW_ON_ERROR) : [],
            name: $request->getName(),
            version: $request->getVersion(),
            channels: $channels,
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     * @throws JsonException
     */
    private function createRefreshRequest(string $body, array $headers): Refresh
    {
        $request = $this->unmarshalRequestBody(DTO\RefreshRequest::class, $body);

        return new Refresh(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true, 512, JSON_THROW_ON_ERROR) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     * @throws JsonException
     */
    private function createSubRefreshRequest(string $body, array $headers): SubRefresh
    {
        $request = $this->unmarshalRequestBody(DTO\SubRefreshRequest::class, $body);

        return new SubRefresh(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            channel: $request->getChannel(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true, 512, JSON_THROW_ON_ERROR) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     * @throws JsonException
     */
    private function createSubscribeRequest(string $body, array $headers): Subscribe
    {
        $request = $this->unmarshalRequestBody(DTO\SubscribeRequest::class, $body);

        return new Subscribe(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            channel: $request->getChannel(),
            token: $request->getToken(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true, 512, JSON_THROW_ON_ERROR) : [],
            data: $request->getData() ? (array)\json_decode($request->getData(), true, 512, JSON_THROW_ON_ERROR) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     * @throws JsonException
     */
    private function createPublishRequest(string $body, array $headers): Publish
    {
        $request = $this->unmarshalRequestBody(DTO\PublishRequest::class, $body);

        return new Publish(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            channel: $request->getChannel(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true, 512, JSON_THROW_ON_ERROR) : [],
            data: $request->getData() ? (array)\json_decode($request->getData(), true, 512, JSON_THROW_ON_ERROR) : [],
            headers: $headers,
        );
    }

    /**
     * @param non-empty-string $body
     * @param RequestHeader $headers
     * @throws JsonException
     */
    private function createRPCRequest(string $body, array $headers): RPC
    {
        $request = $this->unmarshalRequestBody(DTO\RPCRequest::class, $body);

        return new RPC(
            worker: $this->worker,
            client: $request->getClient(),
            transport: $request->getTransport(),
            protocol: $request->getProtocol(),
            encoding: $request->getEncoding(),
            user: $request->getUser(),
            method: $request->getMethod(),
            meta: $request->getMeta() ? (array)\json_decode($request->getMeta(), true, 512, JSON_THROW_ON_ERROR) : [],
            data: $request->getData() ? (array)\json_decode($request->getData(), true, 512, JSON_THROW_ON_ERROR) : [],
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
