<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: service.proto

namespace RoadRunner\Centrifugo\Service\DTO;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>centrifugal.centrifugo.api.BroadcastResult</code>
 */
class BroadcastResult extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .centrifugal.centrifugo.api.PublishResponse responses = 1;</code>
     */
    private $responses;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \RoadRunner\Centrifugo\Service\DTO\PublishResponse[]|\Google\Protobuf\Internal\RepeatedField $responses
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .centrifugal.centrifugo.api.PublishResponse responses = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Generated from protobuf field <code>repeated .centrifugal.centrifugo.api.PublishResponse responses = 1;</code>
     * @param \RoadRunner\Centrifugo\Service\DTO\PublishResponse[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setResponses($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \RoadRunner\Centrifugo\Service\DTO\PublishResponse::class);
        $this->responses = $arr;

        return $this;
    }

}

