<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Stub;

use Google\Protobuf\Internal\Message;
use Spiral\Goridge\Frame;
use Spiral\Goridge\RelayInterface;

/**
 * Stands in for the RoadRunner `centrifuge` RPC plugin on the other end of a Goridge relay: it decodes the
 * protobuf request from the wire and answers with a serialized protobuf response.
 */
final class CentrifugeRpcRelay implements RelayInterface
{
    /** @var non-empty-string|null */
    public ?string $method = null;

    public ?Message $request = null;
    private ?Frame $reply = null;

    /**
     * @param class-string<Message> $requestClass
     */
    public function __construct(
        private readonly string $requestClass,
        private readonly Message $response,
    ) {}

    #[\Override]
    public function send(Frame $frame): void
    {
        [$seq, $methodLength] = $frame->options;
        $payload = (string) $frame->payload;

        $this->method = \substr($payload, 0, $methodLength);
        $this->request = new ($this->requestClass)();
        $this->request->mergeFromString(\substr($payload, $methodLength));

        $this->reply = new Frame(
            $this->method . $this->response->serializeToString(),
            [$seq, \strlen($this->method)],
            Frame::CODEC_PROTO,
        );
    }

    #[\Override]
    public function waitFrame(): Frame
    {
        $reply = $this->reply ?? throw new \LogicException('No request was sent.');
        $this->reply = null;

        return $reply;
    }

    #[\Override]
    public function hasFrame(): bool
    {
        return $this->reply !== null;
    }
}
